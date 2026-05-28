<?php

namespace App\Http\Controllers;

use App\Http\Requests\Booking\SlotsRequest;
use App\Http\Requests\Booking\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Establishment;
use App\Services\AppointmentService;
use App\Services\SlotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AppointmentController extends Controller
{
    public function __construct(private SlotService $slots, private AppointmentService $appointments) {}

    /** Page de prise de rendez-vous. */
    public function create(Establishment $establishment)
    {
        abort_unless($establishment->is_active && $establishment->accepts_bookings, 404);

        $establishment->load([
            'services' => fn ($q) => $q->where('is_bookable', true)->with('category'),
            'practitioners' => fn ($q) => $q->where('is_active', true),
        ]);

        return view('rdv.create', compact('establishment'));
    }

    /** Créneaux libres (AJAX). */
    public function slots(SlotsRequest $request, Establishment $establishment): JsonResponse
    {
        $service = $establishment->services()->where('is_bookable', true)->findOrFail($request->integer('service_id'));
        $practitionerId = $request->filled('practitioner_id') ? $request->integer('practitioner_id') : null;

        // Pas de date : on cherche le premier jour disponible.
        if (! $request->filled('date')) {
            $result = $this->slots->nextAvailability($establishment, $service, $practitionerId);

            return response()->json([
                'date' => $result['date']?->format('Y-m-d'),
                'slots' => $result['slots'],
            ]);
        }

        $date = Carbon::createFromFormat('Y-m-d', $request->input('date'))->startOfDay();

        if ($date->gt(now()->addDays(60))) {
            return response()->json(['date' => $date->format('Y-m-d'), 'slots' => []]);
        }

        return response()->json([
            'date' => $date->format('Y-m-d'),
            'slots' => $this->slots->availableSlots($establishment, $service, $date, $practitionerId),
        ]);
    }

    /** Enregistre le rendez-vous. */
    public function store(StoreAppointmentRequest $request, Establishment $establishment)
    {
        abort_unless($establishment->is_active && $establishment->accepts_bookings, 404);

        $service = $establishment->services()->where('is_bookable', true)->findOrFail($request->integer('service_id'));
        $start = Carbon::createFromFormat('Y-m-d H:i', $request->input('date').' '.$request->input('time'));

        if ($start->isPast() || $start->gt(now()->addDays(60))) {
            return $this->slotError($request, 'Ce créneau n\'est plus disponible.');
        }

        $appointment = $this->appointments->book(
            $establishment,
            $service,
            $start,
            $request->only(['customer_name', 'customer_email', 'customer_phone', 'notes']),
            $request->filled('practitioner_id') ? $request->integer('practitioner_id') : null,
            auth()->id(),
        );

        if (! $appointment) {
            return $this->slotError($request, 'Désolé, ce créneau vient d\'être réservé. Merci d\'en choisir un autre.');
        }

        $this->appointments->notifyConfirmed($appointment);

        if ($request->expectsJson()) {
            $appointment->loadMissing('practitioner');

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
        $cancellable = $this->appointments->cancel($appointment);

        return view('rdv.cancelled', compact('establishment', 'appointment', 'cancellable'));
    }
}
