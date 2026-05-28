<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Establishment;
use App\Models\Practitioner;
use App\Models\TimeOff;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AppointmentController extends Controller
{
    /** Agenda du jour. */
    public function index(Request $request, Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);

        $date = $request->filled('date')
            ? Carbon::createFromFormat('Y-m-d', $request->input('date'))->startOfDay()
            : now()->startOfDay();

        $practitioners = $etablissement->practitioners()
            ->where('is_active', true)
            ->with([
                'appointments' => fn ($q) => $q->whereBetween('starts_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
                    ->whereIn('status', ['confirmed', 'completed', 'no_show'])
                    ->orderBy('starts_at'),
                'timeOffs' => fn ($q) => $q->where('starts_at', '<', $date->copy()->endOfDay())
                    ->where('ends_at', '>', $date->copy()->startOfDay()),
            ])
            ->get();

        $services = $etablissement->services()->orderBy('sort_order')->get();

        return view('client.etablissement.agenda', compact('etablissement', 'practitioners', 'services', 'date'));
    }

    /** Change le statut d'un RDV (honoré, absent, annulé). */
    public function updateStatus(Request $request, Establishment $etablissement, Appointment $appointment)
    {
        $this->authorize('manage', $etablissement);

        $validated = $request->validate([
            'status' => 'required|in:confirmed,cancelled,completed,no_show',
        ]);

        $appointment->update(['status' => $validated['status']]);

        return back()->with('success', 'Rendez-vous mis à jour.');
    }

    /** RDV manuel (téléphone / sur place). */
    public function storeManual(Request $request, Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);

        $validated = $request->validate([
            'practitioner_id' => 'required|integer',
            'service_id' => 'nullable|integer',
            'service_name' => 'required_without:service_id|nullable|string|max:255',
            'duration_minutes' => 'required|integer|min:5|max:600',
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:30',
            'notes' => 'nullable|string|max:1000',
        ]);

        $practitioner = $etablissement->practitioners()->findOrFail($validated['practitioner_id']);

        $service = null;
        if (! empty($validated['service_id'])) {
            $service = $etablissement->services()->find($validated['service_id']);
        }

        $start = Carbon::createFromFormat('Y-m-d H:i', $validated['date'].' '.$validated['time']);
        $end = $start->copy()->addMinutes($validated['duration_minutes']);

        // Anti-chevauchement avec un RDV actif existant du praticien.
        $overlap = $practitioner->appointments()
            ->active()
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->exists();

        if ($overlap) {
            return back()->withInput()->withErrors(['time' => 'Ce praticien a déjà un rendez-vous sur ce créneau.']);
        }

        $etablissement->appointments()->create([
            'practitioner_id' => $practitioner->id,
            'service_id' => $service?->id,
            'service_name' => $service?->name ?? $validated['service_name'],
            'duration_minutes' => $validated['duration_minutes'],
            'customer_name' => $validated['customer_name'],
            'customer_email' => '',
            'customer_phone' => $validated['customer_phone'] ?? null,
            'starts_at' => $start,
            'ends_at' => $end,
            'status' => 'confirmed',
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Rendez-vous ajouté.');
    }

    /** Bloque une plage horaire (congé / pause). */
    public function storeTimeOff(Request $request, Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);

        $validated = $request->validate([
            'practitioner_id' => 'required|integer',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'reason' => 'nullable|string|max:255',
        ]);

        $practitioner = $etablissement->practitioners()->findOrFail($validated['practitioner_id']);

        $practitioner->timeOffs()->create([
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'reason' => $validated['reason'] ?? null,
        ]);

        return back()->with('success', 'Plage bloquée.');
    }

    public function destroyTimeOff(Establishment $etablissement, Practitioner $practitioner, TimeOff $timeOff)
    {
        $this->authorize('manage', $etablissement);

        $timeOff->delete();

        return back()->with('success', 'Plage débloquée.');
    }
}
