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
        $etablissement->load('services');

        return view('client.etablissement.services', compact('etablissement'));
    }

    public function update(Request $request, Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);

        $validated = $request->validate([
            'services' => 'array|max:100',
            'services.*.id' => 'nullable|integer',
            'services.*.name' => 'required|string|max:255',
            'services.*.category' => 'nullable|string|max:100',
            'services.*.description' => 'nullable|string|max:500',
            'services.*.duration_minutes' => 'required|integer|min:5|max:600',
            'services.*.price' => 'nullable|string|max:50',
            'services.*.is_bookable' => 'nullable|boolean',
        ]);

        $rows = collect($validated['services'] ?? [])
            ->filter(fn ($s) => filled($s['name'] ?? null))
            ->values();

        $existingIds = $etablissement->services()->pluck('id')->all();
        $keptIds = [];

        foreach ($rows as $i => $s) {
            $attrs = [
                'name' => trim($s['name']),
                'category' => filled($s['category'] ?? null) ? trim($s['category']) : null,
                'description' => filled($s['description'] ?? null) ? trim($s['description']) : null,
                'duration_minutes' => (int) $s['duration_minutes'],
                'price' => filled($s['price'] ?? null) ? trim($s['price']) : null,
                'is_bookable' => (bool) ($s['is_bookable'] ?? false),
                'sort_order' => $i,
            ];

            // N'accepte un id que s'il appartient bien à cet établissement.
            $id = (isset($s['id']) && in_array((int) $s['id'], $existingIds)) ? (int) $s['id'] : null;

            if ($id) {
                $etablissement->services()->whereKey($id)->update($attrs);
                $keptIds[] = $id;
            } else {
                $keptIds[] = $etablissement->services()->create($attrs)->id;
            }
        }

        // Supprime les prestations retirées du formulaire.
        $etablissement->services()->whereNotIn('id', $keptIds)->delete();

        return back()->with('success', 'Prestations mises à jour.');
    }
}
