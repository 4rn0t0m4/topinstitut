<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Department;
use App\Models\Establishment;
use App\Models\Guide;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $establishments = Establishment::active()
            ->select('id', 'slug', 'type', 'city_id', 'updated_at')
            ->with(['cityRelation:id,slug,department_code', 'cityRelation.department:code,slug'])
            ->orderByDesc('updated_at')
            ->get();

        $departments = Department::select('code', 'slug', 'updated_at')->get();
        $deptByCode = $departments->keyBy('code');

        $cities = City::select('id', 'slug', 'department_code', 'updated_at')
            ->whereHas('establishments', fn ($q) => $q->where('is_active', true))
            ->get()
            ->map(function ($c) use ($deptByCode) {
                $c->department_slug = $deptByCode->get($c->department_code)?->slug;

                return $c;
            })
            ->filter(fn ($c) => $c->department_slug);

        // Type × ville
        $prestations = [];
        foreach ([0, 1, 2, 3] as $type) {
            $typeSlug = Establishment::TYPE_SLUGS[$type];
            $cityIds = Establishment::where('is_active', true)
                ->where('type', $type)
                ->whereNotNull('city_id')
                ->distinct()
                ->pluck('city_id');
            foreach ($cityIds as $cid) {
                $city = $cities->firstWhere('id', $cid);
                if ($city) {
                    $prestations[] = [
                        'slug' => $typeSlug,
                        'city_slug' => $city->slug,
                        'dept_slug' => $city->department_slug,
                    ];
                }
            }
        }

        // Catégorie × ville
        $categoryCombos = DB::table('category_establishment as ce')
            ->join('establishments as e', 'e.id', '=', 'ce.establishment_id')
            ->join('categories as c', 'c.id', '=', 'ce.category_id')
            ->join('cities as ci', 'ci.id', '=', 'e.city_id')
            ->join('departments as d', 'd.code', '=', 'ci.department_code')
            ->where('e.is_active', true)
            ->whereNotNull('c.slug')
            ->select('c.slug as category_slug', 'ci.slug as city_slug', 'd.slug as dept_slug')
            ->distinct()
            ->get();
        foreach ($categoryCombos as $combo) {
            $prestations[] = [
                'slug' => $combo->category_slug,
                'city_slug' => $combo->city_slug,
                'dept_slug' => $combo->dept_slug,
            ];
        }

        $guides = Guide::published()->select('slug', 'updated_at')->get();

        return response()->view('sitemap', compact('establishments', 'departments', 'cities', 'prestations', 'guides'))
            ->header('Content-Type', 'text/xml');
    }
}
