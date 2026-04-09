<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfilController extends Controller
{
    public function edit(Request $request)
    {
        return view('client.profil.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'pseudo' => 'required|string|max:255|unique:users,pseudo,'.$request->user()->id,
            'nom' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'sexe' => 'nullable|in:male,female',
            'adresse' => 'nullable|string|max:255',
            'cp' => 'nullable|string|max:5',
            'ville' => 'nullable|string|max:255',
            'tel_fixe' => 'nullable|string|max:20',
            'tel_port' => 'nullable|string|max:20',
            'anniversaire' => 'nullable|date',
        ]);

        $request->user()->update($validated);

        return back()->with('success', 'Profil mis à jour.');
    }
}
