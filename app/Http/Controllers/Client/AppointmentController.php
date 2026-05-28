<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreManualAppointmentRequest;
use App\Http\Requests\Client\StoreTimeOffRequest;
use App\Http\Requests\Client\UpdateAppointmentStatusRequest;
use App\Models\Appointment;
use App\Models\Establishment;
use App\Models\Practitioner;
use App\Models\TimeOff;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AppointmentController extends Controller
{
    public function __construct(private AppointmentService $appointments) {}

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
    public function updateStatus(UpdateAppointmentStatusRequest $request, Establishment $etablissement, Appointment $appointment)
    {
        $this->appointments->changeStatus($appointment, $request->input('status'));

        return back()->with('success', 'Rendez-vous mis à jour.');
    }

    /** RDV manuel (téléphone / sur place). */
    public function storeManual(StoreManualAppointmentRequest $request, Establishment $etablissement)
    {
        $practitioner = $etablissement->practitioners()->findOrFail($request->integer('practitioner_id'));
        $service = $request->filled('service_id')
            ? $etablissement->services()->find($request->integer('service_id'))
            : null;

        $appointment = $this->appointments->bookManual($etablissement, $practitioner, $service, $request->validated());

        if (! $appointment) {
            return back()->withInput()->withErrors(['time' => 'Ce praticien a déjà un rendez-vous sur ce créneau.']);
        }

        return back()->with('success', 'Rendez-vous ajouté.');
    }

    /** Bloque une plage horaire (congé / pause). */
    public function storeTimeOff(StoreTimeOffRequest $request, Establishment $etablissement)
    {
        $practitioner = $etablissement->practitioners()->findOrFail($request->integer('practitioner_id'));

        $practitioner->timeOffs()->create($request->only(['starts_at', 'ends_at', 'reason']));

        return back()->with('success', 'Plage bloquée.');
    }

    public function destroyTimeOff(Establishment $etablissement, Practitioner $practitioner, TimeOff $timeOff)
    {
        $this->authorize('manage', $etablissement);

        $timeOff->delete();

        return back()->with('success', 'Plage débloquée.');
    }
}
