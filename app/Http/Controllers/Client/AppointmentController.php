<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreManualAppointmentRequest;
use App\Http\Requests\Client\StoreTimeOffRequest;
use App\Http\Requests\Client\UpdateAppointmentRequest;
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

    /** Agenda jour ou semaine. */
    public function index(Request $request, Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);

        $view = $request->input('view') === 'week' ? 'week' : 'day';

        $date = $request->filled('date')
            ? Carbon::createFromFormat('Y-m-d', $request->input('date'))->startOfDay()
            : now()->startOfDay();

        [$rangeStart, $rangeEnd] = $view === 'week'
            ? [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()]
            : [$date->copy()->startOfDay(), $date->copy()->endOfDay()];

        $allPractitioners = $etablissement->practitioners()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $eagerLoad = [
            'appointments' => fn ($q) => $q->whereBetween('starts_at', [$rangeStart, $rangeEnd])
                ->whereIn('status', ['confirmed', 'completed', 'no_show'])
                ->orderBy('starts_at'),
            'timeOffs' => fn ($q) => $q->where('starts_at', '<', $rangeEnd)
                ->where('ends_at', '>', $rangeStart),
        ];

        $selectedPractitioner = null;
        if ($view === 'week' && $allPractitioners->isNotEmpty()) {
            $selectedPractitioner = $request->filled('practitioner_id')
                ? $allPractitioners->firstWhere('id', $request->integer('practitioner_id'))
                : null;
            $selectedPractitioner ??= $allPractitioners->first();
            $selectedPractitioner?->load($eagerLoad);
            $practitioners = $selectedPractitioner ? collect([$selectedPractitioner]) : collect();
        } else {
            // Vue jour : on charge les relations sur la collection Eloquent.
            $allPractitioners->load($eagerLoad);
            $practitioners = $allPractitioners;
        }

        $services = $etablissement->services()->orderBy('sort_order')->get();

        // Jours d'ouverture (1=lundi … 7=dimanche). Si aucun schedule défini,
        // on considère les 7 jours ouverts (pas de filtrage).
        $etablissement->loadMissing('schedules');
        $openDaysOfWeek = $etablissement->schedules->isEmpty()
            ? [1, 2, 3, 4, 5, 6, 7]
            : $etablissement->schedules->where('is_closed', false)->pluck('day_of_week')->map(fn ($v) => (int) $v)->all();

        $weekDays = $view === 'week'
            ? collect(range(0, 6))->map(fn ($i) => $rangeStart->copy()->addDays($i))
                ->filter(fn ($d) => in_array($d->isoWeekday(), $openDaysOfWeek, true))
                ->values()
            : collect();

        return view('client.etablissement.agenda', compact(
            'etablissement', 'practitioners', 'allPractitioners', 'selectedPractitioner',
            'services', 'date', 'view', 'weekDays'
        ));
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

    /** Modifier un RDV (créneau, praticien, prestation, client). */
    public function update(UpdateAppointmentRequest $request, Establishment $etablissement, Appointment $appointment)
    {
        if (! $this->appointments->update($appointment, $etablissement, $request->validated())) {
            return back()->withInput()->withErrors(['time' => 'Ce praticien a déjà un rendez-vous sur ce créneau.']);
        }

        return back()->with('success', 'Rendez-vous modifié.');
    }

    /** Suppression définitive (différent d'une annulation). */
    public function destroy(Establishment $etablissement, Appointment $appointment)
    {
        $this->authorize('manage', $etablissement);

        $this->appointments->delete($appointment);

        return back()->with('success', 'Rendez-vous supprimé.');
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
