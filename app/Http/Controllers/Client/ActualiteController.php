<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Etablissement;
use Illuminate\Http\Request;

class ActualiteController extends Controller
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
        $actualite = $etablissement->actualites()->latest()->first();

        return view('client.actualites.edit', compact('etablissement', 'actualite'));
    }

    public function update(Request $request, Etablissement $etablissement)
    {
        $this->authorize($request, $etablissement);

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'contenu' => 'nullable|string|max:5000',
            'date_limite' => 'nullable|date|after:today',
        ]);

        $validated['etablissement_id'] = $etablissement->id;

        $etablissement->actualites()->updateOrCreate(
            ['etablissement_id' => $etablissement->id],
            $validated
        );

        return back()->with('success', 'Actualité mise à jour.');
    }
}
