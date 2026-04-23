<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Department;
use App\Models\Establishment;
use App\Models\EstablishmentSlug;
use App\Services\GeoSearchService;

class EtablissementController extends Controller
{
    private const TYPE_SLUGS = [
        'institut-de-beaute' => 0,
        'estheticienne-a-domicile' => 1,
        'spa' => 2,
        'thalasso' => 3,
    ];

    /**
     * Hierarchical URL : /{dept}/{city}/{type}/{slug}
     */
    public function showHierarchical(string $dept, string $city, string $type, string $slug, GeoSearchService $geoService)
    {
        if (! isset(self::TYPE_SLUGS[$type])) {
            abort(404);
        }

        $typeId = self::TYPE_SLUGS[$type];

        $department = Department::where('slug', $dept)->firstOrFail();
        $cityModel = City::where('slug', $city)
            ->where('department_code', $department->code)
            ->firstOrFail();

        $establishment = Establishment::where('slug', $slug)
            ->where('city_id', $cityModel->id)
            ->where('type', $typeId)
            ->active()
            ->first();

        if (! $establishment) {
            // Old slug lookup (same city+type)
            $oldSlug = EstablishmentSlug::where('slug', $slug)
                ->whereHas('establishment', function ($q) use ($cityModel, $typeId) {
                    $q->where('city_id', $cityModel->id)->where('type', $typeId);
                })
                ->first();
            if ($oldSlug && $oldSlug->establishment?->is_active) {
                return redirect($oldSlug->establishment->url, 301);
            }
            abort(404);
        }

        return $this->render($establishment, $geoService);
    }

    /**
     * Legacy flat URL : /{type}/{slug} → 301 redirect to hierarchical URL.
     */
    public function show(string $slug, int $type)
    {
        $establishment = Establishment::where('slug', $slug)->where('type', $type)->active()->first();

        if (! $establishment) {
            $oldSlug = EstablishmentSlug::where('slug', $slug)->first();
            if ($oldSlug && $oldSlug->establishment?->is_active) {
                return redirect($oldSlug->establishment->url, 301);
            }

            // Maybe the slug exists but wrong type prefix
            $establishment = Establishment::where('slug', $slug)->active()->first();
            if ($establishment) {
                return redirect($establishment->url, 301);
            }

            abort(404);
        }

        return redirect($establishment->url, 301);
    }

    private function render(Establishment $establishment, GeoSearchService $geoService)
    {
        $establishment->load(['approvedReviews.user', 'photos', 'schedules', 'categories', 'news', 'faqs', 'cityRelation.department', 'owners']);

        $totalInCity = $establishment->city_id
            ? Establishment::active()->where('city_id', $establishment->city_id)->count()
            : 0;

        $nearby = [];
        if ($establishment->latitude && $establishment->longitude) {
            $nearby = $geoService->nearby($establishment->latitude, $establishment->longitude, 10, 5)
                ->where('id', '!=', $establishment->id);
        }

        return view('etablissement.show', compact('establishment', 'nearby', 'totalInCity'));
    }
}
