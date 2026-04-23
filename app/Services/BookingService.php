<?php

namespace App\Services;

use App\Models\Establishment;
use App\Models\Message;
use App\Models\ReviewReminder;
use App\Notifications\NewBookingNotification;
use Carbon\Carbon;
use Illuminate\Support\Str;

class BookingService
{
    /**
     * Persist a booking message, schedule a review reminder and notify the establishment.
     *
     * @param  array<string, mixed>  $data  validated booking form input
     */
    public function create(Establishment $establishment, array $data): Message
    {
        $message = $establishment->messages()->create([
            ...$data,
            'type' => 'booking',
        ]);

        $scheduledAt = $data['requested_date']
            ? Carbon::parse($data['requested_date'])->addDays(7)->setTime(10, 0)
            : now()->addDays(10)->setTime(10, 0);

        ReviewReminder::create([
            'establishment_id' => $establishment->id,
            'message_id' => $message->id,
            'email' => $data['email'],
            'name' => $data['name'] ?? null,
            'scheduled_at' => $scheduledAt,
            'token' => Str::random(48),
        ]);

        if ($establishment->email) {
            $establishment->notify(new NewBookingNotification($message));
        }

        return $message;
    }
}
