<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Services\SlugService;
use Illuminate\Http\Request;

class EtablissementController extends Controller
{
    public function index(Request $request)
    {
        $query = Establishment::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('valide')) {
            $query->where('is_active', $request->boolean('valide'));
        }

        $etablissements = $query->latest()->paginate(25);

        return view('admin.etablissements.index', compact('etablissements'));
    }

    public function show(Establishment $etablissement)
    {
        $etablissement->load(['owners', 'approvedReviews', 'photos']);

        return view('admin.etablissements.show', compact('etablissement'));
    }

    public function edit(Establishment $etablissement)
    {
        return view('admin.etablissements.edit', compact('etablissement'));
    }

    public function update(Request $request, Establishment $etablissement)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|integer|in:0,1,2,3',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:5',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'features' => 'nullable|array',
            'features.*' => ['string', \Illuminate\Validation\Rule::in(array_keys(Establishment::FEATURES))],
            'subscription_tier' => 'nullable|in:free,premium',
            'subscription_ends_at' => 'nullable|date',
            'featured_until' => 'nullable|date',
            'is_verified_owner' => 'boolean',
        ]);

        $validated['features'] = $request->input('features', []);

        $etablissement->update($validated);

        return redirect()->route('admin.etablissements.index')->with('success', 'Établissement mis à jour.');
    }

    public function create()
    {
        return view('admin.etablissements.edit', ['etablissement' => new Establishment]);
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
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = SlugService::generate($validated['name']);
        $validated['is_active'] = true;

        Establishment::create($validated);

        return redirect()->route('admin.etablissements.index')->with('success', 'Établissement créé.');
    }

    public function destroy(Establishment $etablissement)
    {
        $etablissement->delete();

        return redirect()->route('admin.etablissements.index')->with('success', 'Établissement supprimé.');
    }

    public function valider(Establishment $etablissement)
    {
        $etablissement->update(['is_active' => true]);

        return back()->with('success', 'Établissement validé.');
    }
}
