<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\Schedule;
use Illuminate\Http\Request;

class EtablissementController extends Controller
{
    private function authorize(Request $request, Establishment $etablissement): void
    {
        if (! $request->user()->establishments()->where('establishment_id', $etablissement->id)->exists()) {
            abort(403);
        }
    }

    public function edit(Request $request, Establishment $etablissement)
    {
        $this->authorize($request, $etablissement);

        return view('client.etablissement.edit', compact('etablissement'));
    }

    public function update(Request $request, Establishment $etablissement)
    {
        $this->authorize($request, $etablissement);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:5',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $etablissement->update($validated);

        return back()->with('success', 'Établissement mis à jour.');
    }

    public function editPresentation(Request $request, Establishment $etablissement)
    {
        $this->authorize($request, $etablissement);

        return view('client.etablissement.presentation', compact('etablissement'));
    }

    public function updatePresentation(Request $request, Establishment $etablissement)
    {
        $this->authorize($request, $etablissement);

        $validated = $request->validate([
            'description' => 'nullable|string|max:10000',
            'pricing' => 'nullable|string|max:10000',
            'tagline' => 'nullable|string|max:255',
        ]);

        $etablissement->update($validated);

        return back()->with('success', 'Présentation mise à jour.');
    }

    public function editHoraires(Request $request, Establishment $etablissement)
    {
        $this->authorize($request, $etablissement);
        $horaires = $etablissement->schedules->keyBy('day_of_week');

        return view('client.etablissement.horaires', compact('etablissement', 'horaires'));
    }

    public function updateHoraires(Request $request, Establishment $etablissement)
    {
        $this->authorize($request, $etablissement);

        $request->validate([
            'horaires' => 'required|array',
            'horaires.*.is_closed' => 'boolean',
            'horaires.*.open_am' => 'nullable|date_format:H:i',
            'horaires.*.close_am' => 'nullable|date_format:H:i',
            'horaires.*.open_pm' => 'nullable|date_format:H:i',
            'horaires.*.close_pm' => 'nullable|date_format:H:i',
        ]);

        foreach ($request->horaires as $jour => $data) {
            Schedule::updateOrCreate(
                ['establishment_id' => $etablissement->id, 'day_of_week' => $jour],
                [
                    'is_closed' => $data['is_closed'] ?? false,
                    'open_am' => $data['open_am'] ?? null,
                    'close_am' => $data['close_am'] ?? null,
                    'open_pm' => $data['open_pm'] ?? null,
                    'close_pm' => $data['close_pm'] ?? null,
                ]
            );
        }

        return back()->with('success', 'Horaires mis à jour.');
    }

    public function editLocalisation(Request $request, Establishment $etablissement)
    {
        $this->authorize($request, $etablissement);

        return view('client.etablissement.localisation', compact('etablissement'));
    }

    public function updateLocalisation(Request $request, Establishment $etablissement)
    {
        $this->authorize($request, $etablissement);

        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $etablissement->update($validated);

        return back()->with('success', 'Localisation mise à jour.');
    }
}
