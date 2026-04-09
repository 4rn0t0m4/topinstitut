<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LegacyUserProvider extends EloquentUserProvider
{
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        $plain = $credentials['password'];
        $stored = $user->getAuthPassword();

        // 1. Essayer bcrypt (format standard Laravel)
        if (Hash::check($plain, $stored)) {
            return true;
        }

        // 2. Essayer MD5 (format legacy)
        if ($stored === md5($plain)) {
            // Re-hash en bcrypt via DB pour éviter le double-hash du cast 'hashed'
            DB::table('users')
                ->where('id', $user->getAuthIdentifier())
                ->update(['password' => Hash::make($plain)]);

            return true;
        }

        return false;
    }
}
