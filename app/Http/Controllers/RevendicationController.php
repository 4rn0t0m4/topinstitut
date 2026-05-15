<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RevendicationController extends Controller
{
    public function store(Request $request, Establishment $establishment)
    {
        $rules = [
            'manager_name' => 'required|string|max:255',
            'siret' => 'nullable|string|max:14',
            'message' => 'nullable|string|max:2000',
        ];

        // Email obligatoire pour les invités
        if (! $request->user()) {
            $rules['email'] = 'required|email|max:255';
        }

        $validated = $request->validate($rules);

        $email = $request->user()?->email ?? $validated['email'];

        // Déjà propriétaire (uniquement vérifiable pour les connectés) ?
        if ($request->user() && $establishment->owners()->where('user_id', $request->user()->id)->exists()) {
            return $this->errorResponse($request, 'Vous êtes déjà propriétaire de cet établissement.');
        }

        // Demande déjà en cours pour ce couple email/établissement ?
        if (Claim::where('establishment_id', $establishment->id)
            ->where('email', $email)
            ->where('status', 'pending')
            ->exists()) {
            return $this->errorResponse($request, 'Une demande pour cet établissement est déjà en cours avec cet email.');
        }

        $isAuthed = (bool) $request->user();

        $claim = Claim::create([
            'establishment_id' => $establishment->id,
            'user_id' => $request->user()?->id,
            'email' => $email,
            'manager_name' => $validated['manager_name'],
            'siret' => $validated['siret'] ?? null,
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
            // Les connectés ont déjà un email vérifié ; les invités doivent cliquer le lien.
            'email_verified_at' => $isAuthed ? now() : null,
            'verification_token' => $isAuthed ? null : Str::random(64),
        ]);

        if ($isAuthed) {
            $this->notifyAdmin($claim, $establishment);
            $msg = 'Votre demande de propriété a été envoyée. Elle sera vérifiée par notre équipe.';
        } else {
            $this->sendVerificationEmail($claim, $establishment);
            $msg = 'Un email de confirmation vient d\'être envoyé à '.$email.'. Cliquez sur le lien pour valider votre revendication.';
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return back()->with('success', $msg);
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

    private function errorResponse(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => $message], 422);
        }

        return back()->with('error', $message);
    }
}
