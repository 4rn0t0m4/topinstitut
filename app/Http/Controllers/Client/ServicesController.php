<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use Illuminate\Http\Request;

class ServicesController extends Controller
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

        return view('client.etablissement.services', compact('etablissement'));
    }

    public function update(Request $request, Establishment $etablissement)
    {
        $this->authorize($request, $etablissement);

        $request->validate([
            'services' => 'array|max:100',
            'services.*.name' => 'required|string|max:255',
            'services.*.description' => 'nullable|string|max:500',
            'services.*.duration' => 'nullable|string|max:50',
            'services.*.price' => 'nullable|string|max:50',
        ]);

        $services = collect($request->input('services', []))
            ->filter(fn ($s) => ! empty($s['name']))
            ->map(fn ($s) => [
                'name' => trim($s['name']),
                'description' => trim($s['description'] ?? ''),
                'duration' => trim($s['duration'] ?? ''),
                'price' => trim($s['price'] ?? ''),
            ])
            ->values()
            ->all();

        $etablissement->update(['services' => $services ?: null]);

        return back()->with('success', 'Prestations mises à jour.');
    }
}
