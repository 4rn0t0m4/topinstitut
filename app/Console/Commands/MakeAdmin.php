<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MakeAdmin extends Command
{
    protected $signature = 'make:admin';

    protected $description = 'Crée ou met à jour le compte administrateur principal';

    public function handle(): int
    {
        $user = User::updateOrCreate(
            ['email' => 'contact@top-institut.fr'],
            [
                'username' => 'admin',
                'password' => Hash::make('admin1234!'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->info("Admin créé/mis à jour : {$user->email}");

        return self::SUCCESS;
    }
}
