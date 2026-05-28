<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Establishment;
use App\Notifications\AppointmentCancelled;
use App\Notifications\AppointmentConfirmation;
use App\Notifications\NewAppointmentNotification;
use App\Services\SlotService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class AppointmentController extends Controller
{
    public function __construct(private SlotService $slots) {}

    /** Page de prise de rendez-vous. */
    public function create(Establishment $establishment)
    {
        abort_unless($establishment->is_active && $establishment->accepts_bookings, 404);

        $establishment->load(['services' => fn ($q) => $q->where('is_bookable', true)->with('category'), 'practitioners' => fn ($q) => $q->where('is_active', true)]);

        return view('rdv.create', compact('establishment'));
    }

    /** Créneaux libres (AJAX). */
    public function slots(Request $request, Establishment $establishment): JsonResponse
    {
        $validated = $request->validate([
            'service_id' => 'required|integer',
            'practitioner_id' => 'nullable|integer',
            'date' => 'nullable|date_format:Y-m-d|after_or_equal:today',
        ]);

        $service = $establishment->services()->where('is_bookable', true)->findOrFail($validated['service_id']);
        $practitionerId = $validated['practitioner_id'] ?? null;

        // Pas de date : on cherche le premier jour disponible.
        if (empty($validated['date'])) {
            $result = $this->slots->nextAvailability($establishment, $service, $practitionerId);

            return response()->json([
                'date' => $result['date']?->format('Y-m-d'),
                'slots' => $result['slots'],
            ]);
        }

        $date = Carbon::createFromFormat('Y-m-d', $validated['date'])->startOfDay();

        if ($date->gt(now()->addDays(60))) {
            return response()->json(['date' => $date->format('Y-m-d'), 'slots' => []]);
        }

        $slots = $this->slots->availableSlots($establishment, $service, $date, $practitionerId);

        return response()->json(['date' => $date->format('Y-m-d'), 'slots' => $slots]);
    }

    /** Enregistre le rendez-vous. */
    public function store(Request $request, Establishment $establishment)
    {
        abort_unless($establishment->is_active && $establishment->accepts_bookings, 404);

        $validated = $request->validate([
            'service_id' => 'required|integer',
            'practitioner_id' => 'nullable|integer',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:30',
            'notes' => 'nullable|string|max:1000',
        ]);

        $service = $establishment->services()->where('is_bookable', true)->findOrFail($validated['service_id']);
        $start = Carbon::createFromFormat('Y-m-d H:i', $validated['date'].' '.$validated['time']);

        if ($start->isPast() || $start->gt(now()->addDays(60))) {
            return $this->slotError($request, 'Ce créneau n\'est plus disponible.');
        }

        try {
            $appointment = DB::transaction(function () use ($establishment, $service, $start, $validated) {
                // Sérialise les réservations de cet établissement (row lock, indépendant
                // du niveau d'isolation) pour éviter une double réservation simultanée.
                Establishment::whereKey($establishment->id)->lockForUpdate()->first();

                $practitioner = $this->slots->findFreePractitioner(
                    $establishment,
                    $service,
                    $start,
                    $validated['practitioner_id'] ?? null
                );

                if (! $practitioner) {
                    return null;
                }

                return Appointment::create([
                    'establishment_id' => $establishment->id,
                    'practitioner_id' => $practitioner->id,
                    'service_id' => $service->id,
                    'user_id' => auth()->id(),
                    'service_name' => $service->name,
                    'duration_minutes' => $service->duration_minutes,
                    'customer_name' => $validated['customer_name'],
                    'customer_email' => $validated['customer_email'],
                    'customer_phone' => $validated['customer_phone'] ?? null,
                    'starts_at' => $start,
                    'ends_at' => $start->copy()->addMinutes($service->duration_minutes),
                    'status' => 'confirmed',
                    'notes' => $validated['notes'] ?? null,
                ]);
            });
        } catch (QueryException $e) {
            // Filet BDD : violation de l'index unique (practitioner_id, active_slot).
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                $appointment = null;
            } else {
                throw $e;
            }
        }

        if (! $appointment) {
            return $this->slotError($request, 'Désolé, ce créneau vient d\'être réservé. Merci d\'en choisir un autre.');
        }

        // Notifications (client + établissement) — sans bloquer la réservation en cas d'échec mail.
        $appointment->load(['practitioner', 'establishment']);
        try {
            Notification::route('mail', $appointment->customer_email)
                ->notify(new AppointmentConfirmation($appointment));

            if ($establishment->email) {
                $establishment->notify(new NewAppointmentNotification($appointment));
            }
        } catch (\Throwable $e) {
            Log::warning('Echec envoi mail RDV: '.$e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'appointment' => [
                    'establishment' => $establishment->name,
                    'service' => $appointment->service_name,
                    'practitioner' => $appointment->practitioner->name,
                    'date' => $appointment->starts_at->locale('fr')->isoFormat('dddd D MMMM YYYY [à] HH[h]mm'),
                    'email' => $appointment->customer_email,
                    'url' => $establishment->url,
                ],
            ]);
        }

        return redirect()->route('rdv.confirmation', [$establishment, $appointment]);
    }

    private function slotError(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'errors' => ['time' => [$message]]], 422);
        }

        return back()->withInput()->withErrors(['time' => $message]);
    }

    public function confirmation(Establishment $establishment, Appointment $appointment)
    {
        return view('rdv.confirmation', compact('establishment', 'appointment'));
    }

    /** Annulation via lien signé envoyé par email. */
    public function cancel(Establishment $establishment, Appointment $appointment)
    {
        $cancellable = $appointment->status !== 'cancelled' && $appointment->starts_at->isFuture();

        if ($cancellable) {
            $appointment->update(['status' => 'cancelled']);

            try {
                if ($establishment->email) {
                    $establishment->notify(new AppointmentCancelled($appointment));
                }
            } catch (\Throwable $e) {
                Log::warning('Echec envoi mail annulation RDV: '.$e->getMessage());
            }
        }

        return view('rdv.cancelled', compact('establishment', 'appointment', 'cancellable'));
    }
}
