<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use Illuminate\Http\Request;

class ActualiteController extends Controller
{
    public function edit(Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);
        $actualite = $etablissement->news()->latest()->first();

        return view('client.actualites.edit', compact('etablissement', 'actualite'));
    }

    public function update(Request $request, Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string|max:5000',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $validated['establishment_id'] = $etablissement->id;

        $etablissement->news()->updateOrCreate(
            ['establishment_id' => $etablissement->id],
            $validated
        );

        return back()->with('success', 'Actualité mise à jour.');
    }
}
