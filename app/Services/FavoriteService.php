<?php

namespace App\Services;

use App\Models\Establishment;
use App\Models\User;

class FavoriteService
{
    /**
     * Toggle a favorite for the given user. Returns the new state.
     */
    public function toggle(User $user, Establishment $establishment): bool
    {
        if ($user->favorites()->where('establishment_id', $establishment->id)->exists()) {
            $user->favorites()->detach($establishment->id);

            return false;
        }

        $user->favorites()->attach($establishment->id);

        return true;
    }
}
