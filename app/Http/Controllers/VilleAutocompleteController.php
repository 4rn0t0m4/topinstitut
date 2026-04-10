<?php

namespace App\Http\Controllers;

use App\Models\Ville;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VilleAutocompleteController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $villes = Ville::where('nom_ville', 'like', $q . '%')
            ->whereHas('etablissements', fn ($query) => $query->where('valide', true))
            ->orderByDesc('habitants')
            ->limit(8)
            ->get(['nom_ville', 'code_postal', 'url']);

        return response()->json($villes->map(fn ($v) => [
            'label' => $v->nom_ville . ' (' . $v->code_postal . ')',
            'value' => $v->nom_ville,
        ]));
    }
}
