<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RevendicationController extends Controller
{
    public function store(Request $request, Establishment $establishment)
    {
        $validated = $request->validate([
            'manager_name' => 'required|string|max:255',
            'siret' => 'nullable|string|max:14',
            'message' => 'nullable|string|max:2000',
        ]);

        // Déjà propriétaire ?
        if ($establishment->owners()->where('user_id', $request->user()->id)->exists()) {
            return $this->errorResponse($request, 'Vous êtes déjà propriétaire de cet établissement.');
        }

        // Demande déjà en cours ?
        if (Claim::where('establishment_id', $establishment->id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->exists()) {
            return $this->errorResponse($request, 'Vous avez déjà une demande en cours pour cet établissement.');
        }

        $validated['establishment_id'] = $establishment->id;
        $validated['user_id'] = $request->user()->id;
        $validated['status'] = 'pending';

        Claim::create($validated);

        // Notification admin
        Mail::raw(
            "Nouvelle demande de revendication :\n\n"
            ."Établissement : {$establishment->name} (ID: {$establishment->id})\n"
            ."Utilisateur : {$request->user()->email}\n"
            ."Nom gérant : {$validated['manager_name']}\n"
            ."SIRET : ".($validated['siret'] ?: 'Non renseigné')."\n"
            ."Message : ".($validated['message'] ?: '-'),
            function ($mail) use ($establishment) {
                $mail->to(config('mail.from.address', 'contact@top-institut.fr'))
                    ->subject('Revendication : '.$establishment->name);
            }
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Votre demande de propriété a été envoyée. Elle sera vérifiée par notre équipe.');
    }

    private function errorResponse(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => $message], 422);
        }

        return back()->with('error', $message);
    }
}
