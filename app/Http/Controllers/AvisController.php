<?php

namespace App\Http\Controllers;

use App\Models\Avis;
use App\Models\AvisUtile;
use App\Services\RatingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AvisController extends Controller
{
    public function store(Request $request, RatingService $ratingService)
    {
        $rules = [
            'etablissement_id' => 'required|exists:etablissements,id',
            'titre' => 'required|string|max:255',
            'contenu' => 'required|string|max:5000',
            'note_accueil' => 'required|integer|min:1|max:5',
            'note_qualite' => 'required|integer|min:1|max:5',
            'note_choix' => 'required|integer|min:1|max:5',
            'note_prix' => 'required|integer|min:1|max:5',
            'note_cadre' => 'required|integer|min:1|max:5',
            'note_proprete' => 'required|integer|min:1|max:5',
        ];

        // Si non connecté, exiger pseudo et email
        if (! $request->user()) {
            $rules['pseudo_auteur'] = 'required|string|max:255';
            $rules['email_auteur'] = 'required|email|max:255';
        }

        $validated = $request->validate($rules);

        $validated['ip'] = $request->ip();

        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
            $validated['email_verified_at'] = now();
        } else {
            $validated['token_validation'] = Str::random(64);
        }

        $avis = Avis::create($validated);

        // Si non connecté, envoyer l'email de confirmation
        if (! $request->user()) {
            $this->sendConfirmationEmail($avis);

            return back()->with('success', 'Merci ! Un email de confirmation vous a été envoyé à ' . $avis->email_auteur . '. Veuillez cliquer sur le lien pour valider votre avis.');
        }

        return back()->with('success', 'Votre avis a été soumis et sera publié après modération.');
    }

    public function confirmerEmail(string $token)
    {
        $avis = Avis::where('token_validation', $token)
            ->whereNull('email_verified_at')
            ->firstOrFail();

        $avis->update([
            'email_verified_at' => now(),
            'token_validation' => null,
        ]);

        return redirect($avis->etablissement->url)
            ->with('success', 'Votre email a été confirmé. Votre avis sera publié après modération par notre équipe.');
    }

    public function toggleUtile(Request $request)
    {
        $request->validate([
            'avis_id' => 'required|exists:avis,id',
            'utile' => 'required|boolean',
        ]);

        AvisUtile::updateOrCreate(
            ['avis_id' => $request->avis_id, 'user_id' => $request->user()->id],
            ['utile' => $request->boolean('utile')]
        );

        return response()->json(['ok' => true]);
    }

    private function sendConfirmationEmail(Avis $avis): void
    {
        $url = route('avis.confirmer', $avis->token_validation);
        $etablissement = $avis->etablissement->titre;

        Mail::send('emails.avis-confirmation', [
            'pseudo' => $avis->pseudo_auteur,
            'etablissement' => $etablissement,
            'url' => $url,
        ], function ($message) use ($avis) {
            $message->to($avis->email_auteur)
                ->subject('Confirmez votre avis sur Top Institut');
        });
    }
}
