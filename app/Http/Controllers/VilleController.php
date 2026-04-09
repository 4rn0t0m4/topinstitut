<?php

namespace App\Http\Controllers;

use App\Models\Ville;
use App\Models\Etablissement;

class VilleController extends Controller
{
    public function show(string $slug)
    {
        $ville = Ville::where('url', $slug)->firstOrFail();

        $etablissements = Etablissement::valide()
            ->where('ville_id', $ville->id)
            ->orderByDesc('moyenne')
            ->get();

        // Fallback: proximity search if no results in city
        if ($etablissements->isEmpty() && $ville->latitude && $ville->longitude) {
            $etablissements = Etablissement::valide()
                ->nearby($ville->latitude, $ville->longitude, 10)
                ->get();

            if ($etablissements->isEmpty()) {
                $etablissements = Etablissement::valide()
                    ->nearby($ville->latitude, $ville->longitude, 20)
                    ->get();
            }
        }

        return view('ville.show', compact('ville', 'etablissements'));
    }
}
