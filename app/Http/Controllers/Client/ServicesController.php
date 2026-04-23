<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use Illuminate\Http\Request;

class ServicesController extends Controller
{
    public function edit(Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);

        return view('client.etablissement.services', compact('etablissement'));
    }

    public function update(Request $request, Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);

        $request->validate([
            'services' => 'array|max:100',
            'services.*.name' => 'required|string|max:255',
            'services.*.description' => 'nullable|string|max:500',
            'services.*.duration' => 'nullable|string|max:50',
            'services.*.price' => 'nullable|string|max:50',
        ]);

        $etablissement->update([
            'services' => Establishment::normalizeServices($request->input('services', [])),
        ]);

        return back()->with('success', 'Prestations mises à jour.');
    }
}
