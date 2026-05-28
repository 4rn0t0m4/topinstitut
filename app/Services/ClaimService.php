<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\Establishment;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ClaimService
{
    /**
     * Vérifie si l'utilisateur connecté est déjà propriétaire de cet établissement.
     */
    public function isAlreadyOwner(?User $user, Establishment $establishment): bool
    {
        return $user !== null
            && $establishment->owners()->where('user_id', $user->id)->exists();
    }

    /**
     * Vérifie qu'une demande est déjà en cours pour ce couple email/établissement.
     */
    public function hasPendingClaim(Establishment $establishment, string $email): bool
    {
        return Claim::where('establishment_id', $establishment->id)
            ->where('email', $email)
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Crée la demande de revendication, envoie le mail de vérification ou notifie
     * l'admin directement si l'email est déjà prouvé (utilisateur connecté avec
     * son email de compte). Retourne le message à afficher.
     *
     * @param  array{manager_name:string, email:string, siret?:?string, message?:?string}  $data
     */
    public function submit(Establishment $establishment, ?User $user, array $data): string
    {
        $email = $data['email'];
        $emailAlreadyOwned = $user !== null && strcasecmp($email, $user->email) === 0;

        $claim = Claim::create([
            'establishment_id' => $establishment->id,
            'user_id' => $user?->id,
            'email' => $email,
            'manager_name' => $data['manager_name'],
            'siret' => $data['siret'] ?? null,
            'message' => $data['message'] ?? null,
            'status' => 'pending',
            'email_verified_at' => $emailAlreadyOwned ? now() : null,
            'verification_token' => $emailAlreadyOwned ? null : Str::random(64),
        ]);

        if ($emailAlreadyOwned) {
            $this->notifyAdmin($claim, $establishment);

            return 'Votre demande de propriété a été envoyée. Elle sera vérifiée par notre équipe.';
        }

        $this->sendVerificationEmail($claim, $establishment);

        return 'Un email de confirmation vient d\'être envoyé à '.$email.'. Cliquez sur le lien pour valider votre revendication.';
    }

    /**
     * Marque l'email du claim comme vérifié et notifie l'admin.
     */
    public function verifyEmail(Claim $claim): void
    {
        $claim->update([
            'email_verified_at' => now(),
            'verification_token' => null,
        ]);

        $this->notifyAdmin($claim, $claim->establishment);
    }

    private function sendVerificationEmail(Claim $claim, Establishment $establishment): void
    {
        Mail::send('emails.claim-verification', [
            'manager_name' => $claim->manager_name,
            'establishment' => $establishment->name,
            'url' => route('revendication.confirm', $claim->verification_token),
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
