<?php

namespace App\Http\Controllers;

use App\Models\City;
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

        $cities = City::where('name', 'like', $q.'%')
            ->orderByDesc('population')
            ->limit(8)
            ->get(['id', 'name', 'postal_code', 'slug']);

        return response()->json($cities->map(fn ($c) => [
            'id' => $c->id,
            'label' => $c->name.' ('.$c->postal_code.')',
            'value' => $c->name,
            'postal_code' => $c->postal_code,
        ]));
    }
}
