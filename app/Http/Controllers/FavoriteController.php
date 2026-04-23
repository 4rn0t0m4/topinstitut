<?php

namespace App\Http\Controllers;

use App\Models\Establishment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request, Establishment $establishment): JsonResponse
    {
        if ($user = $request->user()) {
            $exists = $user->favorites()->where('establishment_id', $establishment->id)->exists();
            if ($exists) {
                $user->favorites()->detach($establishment->id);
                $state = false;
            } else {
                $user->favorites()->attach($establishment->id);
                $state = true;
            }

            return response()->json(['favorite' => $state, 'authenticated' => true]);
        }

        // Anonymous: managed client-side via localStorage; just acknowledge
        return response()->json(['favorite' => null, 'authenticated' => false]);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $favorites = $user->favorites()
            ->with(['schedules', 'photos', 'cityRelation.department'])
            ->orderByDesc('favorites.created_at')
            ->paginate(20);

        return view('client.favoris', compact('favorites'));
    }
}
