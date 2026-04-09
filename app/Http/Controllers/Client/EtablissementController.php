<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Etablissement;
use App\Models\Horaire;
use Illuminate\Http\Request;

class EtablissementController extends Controller
{
    private function authorize(Request $request, Etablissement $etablissement): void
    {
        if (! $request->user()->etablissements()->where('etablissement_id', $etablissement->id)->exists()) {
            abort(403);
        }
    }

    public function edit(Request $request, Etablissement $etablissement)
    {
        $this->authorize($request, $etablissement);

        return view('client.etablissement.edit', compact('etablissement'));
    }

    public function update(Request $request, Etablissement $etablissement)
    {
        $this->authorize($request, $etablissement);

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'cp' => 'nullable|string|max:5',
            'ville' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'portable' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $etablissement->update($validated);

        return back()->with('success', 'Établissement mis à jour.');
    }

    public function editPresentation(Request $request, Etablissement $etablissement)
    {
        $this->authorize($request, $etablissement);

        return view('client.etablissement.presentation', compact('etablissement'));
    }

    public function updatePresentation(Request $request, Etablissement $etablissement)
    {
        $this->authorize($request, $etablissement);

        $validated = $request->validate([
            'description' => 'nullable|string|max:10000',
            'tarifs' => 'nullable|string|max:10000',
            'accroche' => 'nullable|string|max:255',
        ]);

        $etablissement->update($validated);

        return back()->with('success', 'Présentation mise à jour.');
    }

    public function editHoraires(Request $request, Etablissement $etablissement)
    {
        $this->authorize($request, $etablissement);
        $horaires = $etablissement->horairesRelation->keyBy('jour');

        return view('client.etablissement.horaires', compact('etablissement', 'horaires'));
    }

    public function updateHoraires(Request $request, Etablissement $etablissement)
    {
        $this->authorize($request, $etablissement);

        $request->validate([
            'horaires' => 'required|array',
            'horaires.*.ferme' => 'boolean',
            'horaires.*.matin_ouverture' => 'nullable|date_format:H:i',
            'horaires.*.matin_fermeture' => 'nullable|date_format:H:i',
            'horaires.*.aprem_ouverture' => 'nullable|date_format:H:i',
            'horaires.*.aprem_fermeture' => 'nullable|date_format:H:i',
        ]);

        foreach ($request->horaires as $jour => $data) {
            Horaire::updateOrCreate(
                ['etablissement_id' => $etablissement->id, 'jour' => $jour],
                [
                    'ferme' => $data['ferme'] ?? false,
                    'matin_ouverture' => $data['matin_ouverture'] ?? null,
                    'matin_fermeture' => $data['matin_fermeture'] ?? null,
                    'aprem_ouverture' => $data['aprem_ouverture'] ?? null,
                    'aprem_fermeture' => $data['aprem_fermeture'] ?? null,
                ]
            );
        }

        return back()->with('success', 'Horaires mis à jour.');
    }

    public function editLocalisation(Request $request, Etablissement $etablissement)
    {
        $this->authorize($request, $etablissement);

        return view('client.etablissement.localisation', compact('etablissement'));
    }

    public function updateLocalisation(Request $request, Etablissement $etablissement)
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
