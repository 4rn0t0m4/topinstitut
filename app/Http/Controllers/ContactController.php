<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function showGeneral()
    {
        return view('contact');
    }

    public function sendGeneral(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'contenu' => 'required|string|max:5000',
        ]);

        Mail::raw($request->contenu, function ($message) use ($request) {
            $message->from($request->email)
                ->to(config('mail.from.address'))
                ->subject('Contact TopInstitut');
        });

        return back()->with('success', 'Votre message a bien été envoyé.');
    }

    public function showEtablissement(Etablissement $etablissement)
    {
        return view('contact-etablissement', compact('etablissement'));
    }

    public function sendEtablissement(Request $request, Etablissement $etablissement)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'nom' => 'nullable|string|max:255',
            'contenu' => 'required|string|max:5000',
        ]);

        $validated['etablissement_id'] = $etablissement->id;
        Message::create($validated);

        if ($etablissement->email) {
            Mail::raw($request->contenu, function ($message) use ($request, $etablissement) {
                $message->from($request->email)
                    ->to($etablissement->email)
                    ->subject('Message via TopInstitut - '.$etablissement->titre);
            });
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Votre message a bien été envoyé.');
    }
}
