<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Department;
use App\Models\Establishment;
use App\Models\EstablishmentSlug;
use App\Services\GeoSearchService;

class EtablissementController extends Controller
{
    /**
     * Hierarchical URL : /{dept}/{city}/{type}/{slug}
     */
    public function showHierarchical(string $dept, string $city, string $type, string $slug, GeoSearchService $geoService)
    {
        $typeId = Establishment::typeIdFromSlug($type);
        if ($typeId === null) {
            abort(404);
        }

        $department = Department::where('slug', $dept)->firstOrFail();
        $cityModel = City::where('slug', $city)
            ->where('department_code', $department->code)
            ->firstOrFail();

        $query = Establishment::where('slug', $slug)
            ->where('city_id', $cityModel->id)
            ->where('type', $typeId);

        $establishment = (clone $query)->active()->first()
            ?? (clone $query)->first();

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

        if (! $establishment->is_active && ! $this->canPreview($establishment)) {
            abort(404);
        }

        return $this->render($establishment, $geoService);
    }

    /**
     * Flat URL : /{type}/{slug}
     * - Établissement avec ville résolvable → 301 vers l'URL hiérarchique canonique.
     * - Établissement sans ville (import Google non-matché) → render directement, l'URL plate EST canonique.
     */
    public function show(string $slug, int $type, GeoSearchService $geoService)
    {
        $query = Establishment::where('slug', $slug)->where('type', $type);
        $establishment = (clone $query)->active()->first() ?? (clone $query)->first();

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

        if (! $establishment->is_active && ! $this->canPreview($establishment)) {
            abort(404);
        }

        // Evite la boucle 301 → 301 → … quand l'URL canonique == l'URL courante.
        if ($establishment->is_active && $establishment->url !== '/'.request()->path()) {
            return redirect($establishment->url, 301);
        }

        return $this->render($establishment, $geoService);
    }

    private function canPreview(Establishment $establishment): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        if ($user->is_admin) {
            return true;
        }
        return $establishment->owners()->where('users.id', $user->id)->exists();
    }

    private function render(Establishment $establishment, GeoSearchService $geoService)
    {
        // Compteur de vues : 1 par session toutes les 30 min
        $cacheKey = 'view_'.$establishment->id.'_'.session()->getId();
        if (! cache()->has($cacheKey)) {
            $establishment->increment('view_count');
            cache()->put($cacheKey, true, now()->addMinutes(30));
        }

        $establishment->load(['approvedReviews.user', 'approvedReviews.photos', 'photos', 'schedules', 'services', 'practitioners', 'categories', 'news', 'faqs', 'cityRelation.department', 'owners']);

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
