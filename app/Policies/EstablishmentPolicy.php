<?php

namespace App\Policies;

use App\Models\Establishment;
use App\Models\User;

class EstablishmentPolicy
{
    public function manage(User $user, Establishment $establishment): bool
    {
        return $user->is_admin
            || $user->establishments()->where('establishment_id', $establishment->id)->exists();
    }
}
