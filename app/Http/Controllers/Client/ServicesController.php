<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\UpdateServicesAndCategoriesRequest;
use App\Models\Establishment;
use App\Services\EstablishmentService;

class ServicesController extends Controller
{
    public function edit(Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);
        $etablissement->load([
            'serviceCategories' => fn ($q) => $q->withCount('services'),
            'services',
        ]);

        return view('client.etablissement.services', compact('etablissement'));
    }

    public function update(UpdateServicesAndCategoriesRequest $request, Establishment $etablissement, EstablishmentService $service)
    {
        $service->syncServiceCatalog(
            $etablissement,
            $request->input('categories', []),
            $request->input('services', []),
        );

        return back()->with('success', 'Catégories et prestations mises à jour.');
    }
}
