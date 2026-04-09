<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use App\Models\EtablissementSlug;
use App\Services\GeoSearchService;

class EtablissementController extends Controller
{
    public function show(string $slug, int $type, GeoSearchService $geoService)
    {
        $etablissement = Etablissement::where('slug', $slug)->valide()->first();

        // Try old slug redirect
        if (! $etablissement) {
            $oldSlug = EtablissementSlug::where('slug', $slug)->first();
            if ($oldSlug) {
                $etablissement = $oldSlug->etablissement;
                if ($etablissement?->valide) {
                    return redirect($etablissement->url, 301);
                }
            }
            abort(404);
        }

        // Redirect if type in URL doesn't match
        if ($etablissement->type !== $type) {
            return redirect($etablissement->url, 301);
        }

        $etablissement->load(['approvedAvis.user', 'photos', 'horairesRelation', 'categories', 'actualites']);

        $nearby = [];
        if ($etablissement->latitude && $etablissement->longitude) {
            $nearby = $geoService->nearby($etablissement->latitude, $etablissement->longitude, 10, 5)
                ->where('id', '!=', $etablissement->id);
        }

        return view('etablissement.show', compact('etablissement', 'nearby'));
    }
}
