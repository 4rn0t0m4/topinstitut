<?php

namespace App\Http\Controllers;

use App\Models\Establishment;
use App\Models\Message;
use App\Notifications\NewContactNotification;
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
        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
            'content' => 'required|string|max:5000',
        ]);

        Message::create([
            'establishment_id' => null,
            'type' => 'general',
            ...$validated,
        ]);

        Mail::raw($validated['content'], function ($mail) use ($validated) {
            $mail->from($validated['email'])
                ->to(config('mail.from.address'))
                ->subject('Contact TopInstitut');
        });

        return back()->with('success', 'Votre message a bien été envoyé.');
    }

    public function showEstablishment(Establishment $establishment)
    {
        return view('contact-etablissement', compact('establishment'));
    }

    public function sendEstablishment(Request $request, Establishment $establishment)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
            'content' => 'required|string|max:5000',
        ]);

        $message = $establishment->messages()->create([
            ...$validated,
            'type' => 'contact',
        ]);

        if ($establishment->email) {
            $establishment->notify(new NewContactNotification($message));
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Votre message a bien été envoyé.');
    }
}
