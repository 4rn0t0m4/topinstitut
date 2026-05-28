<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
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

    public function updateSchedules(Request $request, Establishment $etablissement, Practitioner $practitioner)
    {
        $this->authorize('manage', $etablissement);

        $request->validate([
            'days' => 'array',
            'days.*.am_start' => 'nullable|date_format:H:i',
            'days.*.am_end' => 'nullable|date_format:H:i',
            'days.*.pm_start' => 'nullable|date_format:H:i',
            'days.*.pm_end' => 'nullable|date_format:H:i',
        ]);

        $practitioner->schedules()->delete();

        foreach ($request->input('days', []) as $day => $ranges) {
            $day = (int) $day;
            if ($day < 1 || $day > 7) {
                continue;
            }

            foreach ([['am_start', 'am_end'], ['pm_start', 'pm_end']] as [$startKey, $endKey]) {
                $start = $ranges[$startKey] ?? null;
                $end = $ranges[$endKey] ?? null;
                if ($start && $end && $start < $end) {
                    $practitioner->schedules()->create([
                        'day_of_week' => $day,
                        'start_time' => $start,
                        'end_time' => $end,
                    ]);
                }
            }
        }

        return back()->with('success', 'Horaires de travail mis à jour.');
    }
}
