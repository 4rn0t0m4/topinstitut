<?php

namespace App\Http\Controllers;

use App\Http\Concerns\RespondsJsonOrBack;
use App\Models\Claim;
use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RevendicationController extends Controller
{
    use RespondsJsonOrBack;

    public function store(Request $request, Establishment $establishment)
    {
        $validated = $request->validate([
            'manager_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'siret' => 'nullable|string|max:14',
            'message' => 'nullable|string|max:2000',
        ]);

        $email = $validated['email'];

        // Déjà propriétaire (uniquement vérifiable pour les connectés) ?
        if ($request->user() && $establishment->owners()->where('user_id', $request->user()->id)->exists()) {
            return $this->errorJsonOrBack($request, ['email' => 'Vous êtes déjà propriétaire de cet établissement.']);
        }

        // Demande déjà en cours pour ce couple email/établissement ?
        if (Claim::where('establishment_id', $establishment->id)
            ->where('email', $email)
            ->where('status', 'pending')
            ->exists()) {
            return $this->errorJsonOrBack($request, ['email' => 'Une demande pour cet établissement est déjà en cours avec cet email.']);
        }

        // Email déjà prouvé uniquement si l'utilisateur est connecté ET utilise SON email de compte.
        $emailAlreadyOwned = $request->user() && strcasecmp($email, $request->user()->email) === 0;

        $claim = Claim::create([
            'establishment_id' => $establishment->id,
            'user_id' => $request->user()?->id,
            'email' => $email,
            'manager_name' => $validated['manager_name'],
            'siret' => $validated['siret'] ?? null,
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
            'email_verified_at' => $emailAlreadyOwned ? now() : null,
            'verification_token' => $emailAlreadyOwned ? null : Str::random(64),
        ]);

        if ($emailAlreadyOwned) {
            $this->notifyAdmin($claim, $establishment);
            $msg = 'Votre demande de propriété a été envoyée. Elle sera vérifiée par notre équipe.';
        } else {
            $this->sendVerificationEmail($claim, $establishment);
            $msg = 'Un email de confirmation vient d\'être envoyé à '.$email.'. Cliquez sur le lien pour valider votre revendication.';
        }

        return $this->successJsonOrBack($request, $msg);
    }

    public function confirmEmail(string $token)
    {
        $claim = Claim::where('verification_token', $token)
            ->whereNull('email_verified_at')
            ->with('establishment')
            ->firstOrFail();

        $claim->update([
            'email_verified_at' => now(),
            'verification_token' => null,
        ]);

        $this->notifyAdmin($claim, $claim->establishment);

        return redirect($claim->establishment->url)
            ->with('success', 'Votre email a été vérifié. Votre revendication sera traitée par notre équipe sous peu.');
    }

    private function sendVerificationEmail(Claim $claim, Establishment $establishment): void
    {
        $url = route('revendication.confirm', $claim->verification_token);

        Mail::send('emails.claim-verification', [
            'manager_name' => $claim->manager_name,
            'establishment' => $establishment->name,
            'url' => $url,
        ], function ($mail) use ($claim, $establishment) {
            $mail->to($claim->email)
                ->subject('Confirmez votre revendication de '.$establishment->name);
        });
    }

    private function notifyAdmin(Claim $claim, Establishment $establishment): void
    {
        Mail::raw(
            "Nouvelle demande de revendication (email vérifié) :\n\n"
            ."Établissement : {$establishment->name} (ID: {$establishment->id})\n"
            ."Email : {$claim->email}\n"
            ."Compte existant : ".($claim->user_id ? 'oui' : 'non — sera créé à l\'approbation')."\n"
            ."Nom gérant : {$claim->manager_name}\n"
            ."SIRET : ".($claim->siret ?: 'Non renseigné')."\n"
            ."Message : ".($claim->message ?: '-'),
            function ($mail) use ($establishment) {
                $mail->to(config('mail.from.address', 'contact@top-institut.fr'))
                    ->subject('Revendication : '.$establishment->name);
            }
        );
    }

}
