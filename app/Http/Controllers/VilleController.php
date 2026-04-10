<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use App\Models\Ville;

class VilleController extends Controller
{
    public function show(string $slug)
    {
        $ville = Ville::where('url', $slug)->firstOrFail();

        $query = Etablissement::valide()->where('ville_id', $ville->id)
            ->orderByRaw('classement_ville = 0 ASC, classement_ville ASC, moyenne DESC');

        if ($query->count() === 0 && $ville->latitude && $ville->longitude) {
            $query = Etablissement::valide()->nearby($ville->latitude, $ville->longitude, 15);
        }

        $etablissements = $query->paginate(20);

        return view('ville.show', compact('ville', 'etablissements'));
    }
}
