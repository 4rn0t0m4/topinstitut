<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    public function index()
    {
        $categories = Categorie::with('children')->whereNull('parent_id')->orderBy('nom')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Categorie::whereNull('parent_id')->orderBy('nom')->get();

        return view('admin.categories.edit', ['categorie' => new Categorie(), 'parents' => $parents]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
        ]);

        Categorie::create($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie créée.');
    }

    public function edit(Categorie $categorie)
    {
        $parents = Categorie::whereNull('parent_id')->where('id', '!=', $categorie->id)->orderBy('nom')->get();

        return view('admin.categories.edit', compact('categorie', 'parents'));
    }

    public function update(Request $request, Categorie $categorie)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
        ]);

        $categorie->update($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(Categorie $categorie)
    {
        $categorie->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie supprimée.');
    }
}
