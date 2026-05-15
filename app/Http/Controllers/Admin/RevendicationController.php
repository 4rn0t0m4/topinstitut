<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class RevendicationController extends Controller
{
    public function index()
    {
        // Seules les demandes dont l'email est vérifié arrivent ici
        $revendications = Claim::where('status', 'pending')
            ->whereNotNull('email_verified_at')
            ->with(['establishment', 'user'])
            ->latest()
            ->paginate(25);

        return view('admin.revendications.index', compact('revendications'));
    }

    public function moderer(Request $request, Claim $revendication)
    {
        $request->validate(['action' => 'required|in:approuver,refuser']);

        if ($request->action === 'refuser') {
            $revendication->update(['status' => 'rejected']);

            return redirect()->route('admin.revendications.index')->with('success', 'Demande refusée.');
        }

        // === Approbation ===
        $userId = $revendication->user_id;

        if (! $userId) {
            // Pas de compte associé : on en cherche un avec cet email, sinon on le crée.
            $user = User::where('email', $revendication->email)->first();

            if (! $user) {
                $user = User::create([
                    'username' => $this->generateUsername($revendication->manager_name, $revendication->email),
                    'email' => $revendication->email,
                    'first_name' => $revendication->manager_name,
                    'password' => Str::random(32), // sera réinitialisé via mail
                    'email_verified_at' => now(), // email déjà vérifié au moment de la revendication
                ]);

                // Envoi du lien "définir mot de passe" via le flow Laravel password reset
                Password::sendResetLink(['email' => $user->email]);
            }

            $userId = $user->id;
            $revendication->update(['user_id' => $userId]);
        }

        $revendication->update(['status' => 'approved']);
        $revendication->establishment->owners()->syncWithoutDetaching([$userId]);

        return redirect()->route('admin.revendications.index')->with('success', 'Demande approuvée — propriétaire ajouté.');
    }

    /**
     * Génère un username unique à partir du nom et/ou de l'email.
     */
    private function generateUsername(string $name, string $email): string
    {
        $base = Str::slug(Str::lower($name)) ?: Str::before($email, '@');
        $username = $base;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base.$i;
            $i++;
        }

        return $username;
    }
}
