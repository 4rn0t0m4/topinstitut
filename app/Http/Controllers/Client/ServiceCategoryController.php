<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function index(Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);
        $etablissement->load(['serviceCategories' => fn ($q) => $q->withCount('services')]);

        return view('client.etablissement.categories.index', compact('etablissement'));
    }

    public function update(Request $request, Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);

        $validated = $request->validate([
            'categories' => 'array|max:50',
            'categories.*.id' => 'nullable|integer',
            'categories.*.name' => 'required|string|max:100',
        ]);

        $rows = collect($validated['categories'] ?? [])
            ->filter(fn ($c) => filled($c['name'] ?? null))
            ->values();

        $existingIds = $etablissement->serviceCategories()->pluck('id')->all();
        $keptIds = [];

        foreach ($rows as $i => $c) {
            $attrs = ['name' => trim($c['name']), 'sort_order' => $i];
            $id = (isset($c['id']) && in_array((int) $c['id'], $existingIds)) ? (int) $c['id'] : null;

            if ($id) {
                $etablissement->serviceCategories()->whereKey($id)->update($attrs);
                $keptIds[] = $id;
            } else {
                $keptIds[] = $etablissement->serviceCategories()->create($attrs)->id;
            }
        }

        // Catégories retirées : la FK nullOnDelete remet service_category_id à null
        // sur les prestations concernées (elles deviennent « Sans catégorie »).
        $etablissement->serviceCategories()->whereNotIn('id', $keptIds)->delete();

        return back()->with('success', 'Catégories mises à jour.');
    }
}
