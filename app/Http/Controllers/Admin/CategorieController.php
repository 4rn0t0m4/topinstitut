<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    public function index()
    {
        $categories = Category::with('children')->whereNull('parent_id')->orderBy('name')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::whereNull('parent_id')->orderBy('name')->get();

        return view('admin.categories.edit', ['categorie' => new Category, 'parents' => $parents]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
        ]);

        Category::create($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie créée.');
    }

    public function edit(Category $categorie)
    {
        $parents = Category::whereNull('parent_id')->where('id', '!=', $categorie->id)->orderBy('name')->get();

        return view('admin.categories.edit', compact('categorie', 'parents'));
    }

    public function update(Request $request, Category $categorie)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
        ]);

        $categorie->update($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(Category $categorie)
    {
        $categorie->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie supprimée.');
    }
}
