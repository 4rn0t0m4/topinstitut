<?php

namespace App\Http\Controllers;

use App\Models\Departement;

class DepartementController extends Controller
{
    public function show(string $slug)
    {
        $departement = Departement::where('departement_url', $slug)->firstOrFail();
        $villes = $departement->villes()
            ->withCount(['etablissements' => fn ($q) => $q->where('valide', true)])
            ->having('etablissements_count', '>', 0)
            ->orderBy('nom_ville')
            ->get();

        return view('departement.show', compact('departement', 'villes'));
    }
}
