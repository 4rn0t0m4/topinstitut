<?php

namespace App\Http\Controllers;

use App\Models\Establishment;
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
            'name' => 'required|string|max:255',
            'type' => 'required|integer|in:0,1,2,3',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:5',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string|max:5000',
        ]);

        $validated['slug'] = SlugService::generate($validated['name']);
        $validated['is_active'] = false;

        $establishment = Establishment::create($validated);

        if (Auth::check()) {
            $establishment->owners()->attach(Auth::id());
        }

        return redirect()->route('home')->with('success', 'Votre établissement a été enregistré et sera validé prochainement.');
    }
}
