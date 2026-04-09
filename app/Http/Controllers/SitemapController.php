<?php

namespace App\Http\Controllers;

use App\Models\Departement;
use App\Models\Etablissement;
use App\Models\Ville;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $etablissements = Etablissement::valide()
            ->select('slug', 'type', 'updated_at')
            ->orderByDesc('updated_at')
            ->get();

        $departements = Departement::select('departement_url', 'updated_at')->get();

        $villes = Ville::select('url', 'updated_at')
            ->whereHas('etablissements', fn ($q) => $q->where('valide', true))
            ->get();

        return response()->view('sitemap', compact('etablissements', 'departements', 'villes'))
            ->header('Content-Type', 'text/xml');
    }
}
