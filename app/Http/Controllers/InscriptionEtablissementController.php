<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use App\Services\SlugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InscriptionEtablissementController extends Controller
{
    public function create()
    {
        return view('inscription-etablissement');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'type' => 'required|integer|in:0,1,2,3',
            'adresse' => 'nullable|string|max:255',
            'cp' => 'nullable|string|max:5',
            'ville' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string|max:5000',
        ]);

        $validated['slug'] = SlugService::generate($validated['titre']);
        $validated['valide'] = false;

        $etablissement = Etablissement::create($validated);

        if (Auth::check()) {
            $etablissement->administrateurs()->attach(Auth::id());
        }

        return redirect()->route('home')->with('success', 'Votre établissement a été enregistré et sera validé prochainement.');
    }
}
