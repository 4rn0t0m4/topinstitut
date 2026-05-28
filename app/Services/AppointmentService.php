<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Establishment;
use App\Models\Practitioner;
use App\Models\Service;
use App\Notifications\AppointmentCancelled;
use App\Notifications\AppointmentConfirmation;
use App\Notifications\NewAppointmentNotification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class AppointmentService
{
    public function __construct(private SlotService $slots) {}

    /**
     * Réservation publique : recherche un praticien libre, verrouille l'établissement
     * et crée le RDV. Retourne null si le créneau a été pris entre temps (concurrence).
     *
     * @param  array{customer_name:string, customer_email:string, customer_phone?:?string, notes?:?string}  $customer
     */
    public function book(Establishment $establishment, Service $service, Carbon $start, array $customer, ?int $practitionerId, ?int $userId): ?Appointment
    {
        try {
            return DB::transaction(function () use ($establishment, $service, $start, $customer, $practitionerId, $userId) {
                // Row lock sur l'établissement : sérialise toutes les réservations
                // concurrentes, indépendamment du niveau d'isolation.
                Establishment::whereKey($establishment->id)->lockForUpdate()->first();

                $practitioner = $this->slots->findFreePractitioner($establishment, $service, $start, $practitionerId);
                if (! $practitioner) {
                    return null;
                }

                return Appointment::create([
                    'establishment_id' => $establishment->id,
                    'practitioner_id' => $practitioner->id,
                    'service_id' => $service->id,
                    'user_id' => $userId,
                    'service_name' => $service->name,
                    'duration_minutes' => $service->duration_minutes,
                    'customer_name' => $customer['customer_name'],
                    'customer_email' => $customer['customer_email'],
                    'customer_phone' => $customer['customer_phone'] ?? null,
                    'starts_at' => $start,
                    'ends_at' => $start->copy()->addMinutes($service->duration_minutes),
                    'status' => 'confirmed',
                    'notes' => $customer['notes'] ?? null,
                ]);
            });
        } catch (QueryException $e) {
            // Filet BDD : violation de l'index unique (practitioner_id, active_slot).
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * Envoie les emails de confirmation (client + établissement). Non bloquant.
     */
    public function notifyConfirmed(Appointment $appointment): void
    {
        $appointment->loadMissing(['practitioner', 'establishment']);
        try {
            Notification::route('mail', $appointment->customer_email)
                ->notify(new AppointmentConfirmation($appointment));

            if ($appointment->establishment->email) {
                $appointment->establishment->notify(new NewAppointmentNotification($appointment));
            }
        } catch (\Throwable $e) {
            Log::warning('Echec envoi mail RDV: '.$e->getMessage());
        }
    }

    /**
     * RDV manuel (saisi par le gérant). Vérifie l'absence de chevauchement.
     * Retourne null si conflit.
     *
     * @param  array{date:string, time:string, duration_minutes:int|string, customer_name:string, customer_phone?:?string, service_name?:?string, notes?:?string}  $data
     */
    public function bookManual(Establishment $establishment, Practitioner $practitioner, ?Service $service, array $data): ?Appointment
    {
        $start = Carbon::createFromFormat('Y-m-d H:i', $data['date'].' '.$data['time']);
        $duration = (int) $data['duration_minutes'];
        $end = $start->copy()->addMinutes($duration);

        $overlap = $practitioner->appointments()
            ->active()
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->exists();

        if ($overlap) {
            return null;
        }

        return $establishment->appointments()->create([
            'practitioner_id' => $practitioner->id,
            'service_id' => $service?->id,
            'service_name' => $service?->name ?? ($data['service_name'] ?? ''),
            'duration_minutes' => $duration,
            'customer_name' => $data['customer_name'],
            'customer_email' => '',
            'customer_phone' => $data['customer_phone'] ?? null,
            'starts_at' => $start,
            'ends_at' => $end,
            'status' => 'confirmed',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Annule un RDV s'il est encore annulable (futur + non déjà annulé).
     * Notifie l'établissement. Retourne true si effectivement annulé.
     */
    public function cancel(Appointment $appointment): bool
    {
        if ($appointment->status === 'cancelled' || ! $appointment->starts_at->isFuture()) {
            return false;
        }

        $appointment->update(['status' => 'cancelled']);

        try {
            $appointment->loadMissing('establishment');
            if ($appointment->establishment->email) {
                $appointment->establishment->notify(new AppointmentCancelled($appointment));
            }
        } catch (\Throwable $e) {
            Log::warning('Echec envoi mail annulation RDV: '.$e->getMessage());
        }

        return true;
    }

    /**
     * Change le statut d'un RDV (confirmed, cancelled, completed, no_show).
     */
    public function changeStatus(Appointment $appointment, string $status): void
    {
        $appointment->update(['status' => $status]);
    }
}
