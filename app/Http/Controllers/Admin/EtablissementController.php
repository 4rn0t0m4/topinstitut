<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Etablissement;
use App\Services\SlugService;
use Illuminate\Http\Request;

class EtablissementController extends Controller
{
    public function index(Request $request)
    {
        $query = Etablissement::query();

        if ($request->filled('search')) {
            $query->where('titre', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('valide')) {
            $query->where('valide', $request->boolean('valide'));
        }

        $etablissements = $query->latest()->paginate(25);

        return view('admin.etablissements.index', compact('etablissements'));
    }

    public function show(Etablissement $etablissement)
    {
        $etablissement->load(['administrateurs', 'approvedAvis', 'photos']);

        return view('admin.etablissements.show', compact('etablissement'));
    }

    public function edit(Etablissement $etablissement)
    {
        return view('admin.etablissements.edit', compact('etablissement'));
    }

    public function update(Request $request, Etablissement $etablissement)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'type' => 'required|integer|in:0,1,2,3',
            'adresse' => 'nullable|string|max:255',
            'cp' => 'nullable|string|max:5',
            'ville' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'valide' => 'boolean',
        ]);

        $etablissement->update($validated);

        return redirect()->route('admin.etablissements.index')->with('success', 'Établissement mis à jour.');
    }

    public function create()
    {
        return view('admin.etablissements.edit', ['etablissement' => new Etablissement]);
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
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = SlugService::generate($validated['titre']);
        $validated['valide'] = true;

        Etablissement::create($validated);

        return redirect()->route('admin.etablissements.index')->with('success', 'Établissement créé.');
    }

    public function destroy(Etablissement $etablissement)
    {
        $etablissement->delete();

        return redirect()->route('admin.etablissements.index')->with('success', 'Établissement supprimé.');
    }

    public function valider(Etablissement $etablissement)
    {
        $etablissement->update(['valide' => true]);

        return back()->with('success', 'Établissement validé.');
    }
}
