<?php

namespace App\Http\Controllers;

use App\Models\Establishment;
use App\Services\FavoriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request, Establishment $establishment, FavoriteService $favorites): JsonResponse
    {
        if (! $user = $request->user()) {
            return response()->json(['favorite' => null, 'authenticated' => false]);
        }

        return response()->json([
            'favorite' => $favorites->toggle($user, $establishment),
            'authenticated' => true,
        ]);
    }

    public function index(Request $request)
    {
        $favorites = $request->user()
            ->favorites()
            ->with(['schedules', 'photos', 'cityRelation.department'])
            ->orderByDesc('favorites.created_at')
            ->paginate(20);

        return view('client.favoris', compact('favorites'));
    }
}
