<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\UpdateEstablishmentBasicInfoRequest;
use App\Http\Requests\Client\UpdateHorairesRequest;
use App\Http\Requests\Client\UpdateLocalisationRequest;
use App\Http\Requests\Client\UpdatePresentationRequest;
use App\Models\Establishment;
use App\Services\EstablishmentService;

class EtablissementController extends Controller
{
    public function edit(Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);

        return view('client.etablissement.edit', compact('etablissement'));
    }

    public function update(UpdateEstablishmentBasicInfoRequest $request, Establishment $etablissement, EstablishmentService $service)
    {
        $service->updateBasicInfo($etablissement, $request->validated());

        return back()->with('success', 'Établissement mis à jour.');
    }

    public function editPresentation(Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);

        return view('client.etablissement.presentation', compact('etablissement'));
    }

    public function updatePresentation(UpdatePresentationRequest $request, Establishment $etablissement, EstablishmentService $service)
    {
        $service->updatePresentation($etablissement, $request->validated());

        return back()->with('success', 'Présentation mise à jour.');
    }

    public function editHoraires(Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);
        $horaires = $etablissement->schedules->keyBy('day_of_week');

        return view('client.etablissement.horaires', compact('etablissement', 'horaires'));
    }

    public function updateHoraires(UpdateHorairesRequest $request, Establishment $etablissement, EstablishmentService $service)
    {
        $service->updateSchedules($etablissement, $request->horaires);

        return back()->with('success', 'Horaires mis à jour.');
    }

    public function editLocalisation(Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);

        return view('client.etablissement.localisation', compact('etablissement'));
    }

    public function updateLocalisation(UpdateLocalisationRequest $request, Establishment $etablissement, EstablishmentService $service)
    {
        $service->updateLocation($etablissement, $request->validated());

        return back()->with('success', 'Localisation mise à jour.');
    }
}
