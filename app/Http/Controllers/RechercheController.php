<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use App\Models\Ville;
use Illuminate\Http\Request;

class RechercheController extends Controller
{
    public function index(Request $request)
    {
        $query = Etablissement::valide();

        $nom = $request->input('nom');
        $villeNom = $request->input('ville');
        $categorie = $request->input('categorie');

        if ($nom) {
            $query->where('titre', 'like', '%' . $nom . '%');
        }

        if ($villeNom) {
            $ville = Ville::where('nom_ville', 'like', $villeNom)->first();
            if ($ville) {
                if ($ville->latitude && $ville->longitude) {
                    $query->nearby($ville->latitude, $ville->longitude, 15);
                } else {
                    $query->where('ville_id', $ville->id);
                }
            }
        }

        if ($categorie) {
            $query->whereHas('categories', fn ($q) => $q->where('categories.id', $categorie));
        }

        $etablissements = $query->orderByDesc('moyenne')->paginate(20);

        return view('recherche.index', compact('etablissements', 'nom', 'villeNom', 'categorie'));
    }
}
