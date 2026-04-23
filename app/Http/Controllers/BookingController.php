<?php

namespace App\Http\Controllers;

use App\Models\Establishment;
use App\Models\Message;
use App\Models\ReviewReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function store(Request $request, Establishment $establishment)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'requested_date' => 'required|date|after_or_equal:today',
            'requested_time' => 'required|string|max:20',
            'requested_service' => 'nullable|string|max:255',
            'content' => 'nullable|string|max:2000',
        ]);

        $validated['establishment_id'] = $establishment->id;
        $validated['type'] = 'booking';

        $message = Message::create($validated);

        // Schedule review reminder 7 days after the requested date
        $scheduledAt = $validated['requested_date'] instanceof \DateTimeInterface
            ? \Carbon\Carbon::parse($validated['requested_date'])->addDays(7)->setTime(10, 0)
            : now()->addDays(10)->setTime(10, 0);

        ReviewReminder::create([
            'establishment_id' => $establishment->id,
            'message_id' => $message->id,
            'email' => $validated['email'],
            'name' => $validated['name'],
            'scheduled_at' => $scheduledAt,
            'token' => Str::random(48),
        ]);

        if ($establishment->email) {
            $body = "Demande de RDV via TopInstitut\n\n"
                ."Nom : {$validated['name']}\n"
                ."Email : {$validated['email']}\n"
                .(! empty($validated['phone']) ? "Téléphone : {$validated['phone']}\n" : '')
                ."Date souhaitée : ".$validated['requested_date']."\n"
                ."Horaire : ".$validated['requested_time']."\n"
                .(! empty($validated['requested_service']) ? 'Prestation : '.$validated['requested_service']."\n" : '')
                .(! empty($validated['content']) ? "\nMessage :\n".$validated['content']."\n" : '');

            Mail::raw($body, function ($mail) use ($validated, $establishment) {
                $mail->from($validated['email'], $validated['name'])
                    ->to($establishment->email)
                    ->subject('Demande de RDV - '.$establishment->name);
            });
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Demande envoyée.']);
        }

        return back()->with('success', 'Votre demande de RDV a bien été envoyée.');
    }
}
