<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategorieAutocompleteController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $categories = Category::where('name', 'like', '%'.$q.'%')
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name']);

        return response()->json($categories);
    }
}
