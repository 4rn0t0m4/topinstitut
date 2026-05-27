<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Notifications\AppointmentReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:remind';

    protected $description = 'Envoie les rappels de RDV : la veille (J-1) et le jour même';

    public function handle(): int
    {
        $sent = 0;

        // Rappel J-1 : RDV de demain pas encore relancés.
        $tomorrow = now()->addDay();
        $dayBefore = Appointment::query()
            ->where('status', 'confirmed')
            ->whereNull('reminded_day_before_at')
            ->whereBetween('starts_at', [$tomorrow->copy()->startOfDay(), $tomorrow->copy()->endOfDay()])
            ->with(['establishment', 'practitioner'])
            ->get();

        foreach ($dayBefore as $appointment) {
            if ($this->notify($appointment, sameDay: false)) {
                $appointment->update(['reminded_day_before_at' => now()]);
                $sent++;
            }
        }

        // Rappel jour J : RDV d'aujourd'hui encore à venir, pas encore relancés.
        $today = Appointment::query()
            ->where('status', 'confirmed')
            ->whereNull('reminded_same_day_at')
            ->whereBetween('starts_at', [now()->startOfDay(), now()->endOfDay()])
            ->with(['establishment', 'practitioner'])
            ->get();

        foreach ($today as $appointment) {
            if ($this->notify($appointment, sameDay: true)) {
                $appointment->update(['reminded_same_day_at' => now()]);
                $sent++;
            }
        }

        $this->info("Rappels envoyés : {$sent}");

        return self::SUCCESS;
    }

    private function notify(Appointment $appointment, bool $sameDay): bool
    {
        if (! $appointment->customer_email) {
            return false;
        }

        try {
            Notification::route('mail', $appointment->customer_email)
                ->notify(new AppointmentReminder($appointment, $sameDay));

            return true;
        } catch (\Throwable $e) {
            $this->warn('Echec rappel RDV #'.$appointment->id.' : '.$e->getMessage());

            return false;
        }
    }
}
