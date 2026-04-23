<?php

namespace App\Http\Controllers;

use App\Models\Establishment;
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
            'content' => 'required|string|max:5000',
        ]);

        Mail::raw($request->content, function ($message) use ($request) {
            $message->from($request->email)
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

        $validated['establishment_id'] = $establishment->id;
        Message::create($validated);

        if ($establishment->email) {
            Mail::raw($request->content, function ($message) use ($request, $establishment) {
                $message->from($request->email)
                    ->to($establishment->email)
                    ->subject('Message via TopInstitut - '.$establishment->name);
            });
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Votre message a bien été envoyé.');
    }
}
