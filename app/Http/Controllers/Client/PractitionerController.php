<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\UpdatePractitionerSchedulesRequest;
use App\Models\Establishment;
use App\Models\Practitioner;
use Illuminate\Http\Request;

class PractitionerController extends Controller
{
    public function index(Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);
        $etablissement->load('practitioners');

        return view('client.etablissement.praticiens.index', compact('etablissement'));
    }

    public function store(Request $request, Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);

        $validated = $request->validate(['name' => 'required|string|max:255']);

        $etablissement->practitioners()->create([
            'name' => $validated['name'],
            'sort_order' => $etablissement->practitioners()->count(),
        ]);

        return back()->with('success', 'Praticien ajouté.');
    }

    public function update(Request $request, Establishment $etablissement, Practitioner $practitioner)
    {
        $this->authorize('manage', $etablissement);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $practitioner->update([
            'name' => $validated['name'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Praticien mis à jour.');
    }

    public function destroy(Establishment $etablissement, Practitioner $practitioner)
    {
        $this->authorize('manage', $etablissement);

        $practitioner->delete();

        return back()->with('success', 'Praticien supprimé.');
    }

    public function editSchedules(Establishment $etablissement, Practitioner $practitioner)
    {
        $this->authorize('manage', $etablissement);

        $schedules = $practitioner->schedules->groupBy('day_of_week');

        return view('client.etablissement.praticiens.horaires', compact('etablissement', 'practitioner', 'schedules'));
    }

    public function updateSchedules(UpdatePractitionerSchedulesRequest $request, Establishment $etablissement, Practitioner $practitioner)
    {
        $practitioner->syncSchedules($request->input('days', []));

        return back()->with('success', 'Horaires de travail mis à jour.');
    }
}
