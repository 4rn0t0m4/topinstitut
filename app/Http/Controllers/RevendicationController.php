<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use App\Models\Revendication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RevendicationController extends Controller
{
    public function store(Request $request, Etablissement $etablissement)
    {
        $validated = $request->validate([
            'nom_gerant' => 'required|string|max:255',
            'siret' => 'nullable|string|max:14',
            'message' => 'nullable|string|max:2000',
        ]);

        // Vérifier qu'il n'est pas déjà admin
        if ($etablissement->administrateurs()->where('user_id', $request->user()->id)->exists()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Vous êtes déjà propriétaire de cet établissement.'], 422);
            }

            return back()->with('error', 'Vous êtes déjà propriétaire de cet établissement.');
        }

        // Vérifier qu'il n'a pas déjà une demande en attente
        if (Revendication::where('etablissement_id', $etablissement->id)
            ->where('user_id', $request->user()->id)
            ->where('statut', 'en_attente')
            ->exists()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Vous avez déjà une demande en cours pour cet établissement.'], 422);
            }

            return back()->with('error', 'Vous avez déjà une demande en cours.');
        }

        $validated['etablissement_id'] = $etablissement->id;
        $validated['user_id'] = $request->user()->id;

        Revendication::create($validated);

        // Notification admin
        Mail::raw(
            "Nouvelle demande de revendication :\n\n"
            ."Établissement : {$etablissement->titre} (ID: {$etablissement->id})\n"
            ."Utilisateur : {$request->user()->email}\n"
            ."Nom gérant : {$validated['nom_gerant']}\n"
            ."SIRET : ".($validated['siret'] ?: 'Non renseigné')."\n"
            ."Message : ".($validated['message'] ?: '-'),
            function ($message) use ($etablissement) {
                $message->to(config('mail.from.address', 'contact@top-institut.fr'))
                    ->subject('Revendication : '.$etablissement->titre);
            }
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Votre demande de propriété a été envoyée. Elle sera vérifiée par notre équipe.');
    }
}
