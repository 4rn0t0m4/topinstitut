<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServicesController extends Controller
{
    public function edit(Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);
        $etablissement->load([
            'serviceCategories' => fn ($q) => $q->withCount('services'),
            'services',
        ]);

        return view('client.etablissement.services', compact('etablissement'));
    }

    public function update(Request $request, Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);

        $validated = $request->validate([
            'categories' => 'array|max:50',
            'categories.*.cid' => 'required|string|max:20',
            'categories.*.id' => 'nullable|integer',
            'categories.*.name' => 'required|string|max:100',
            'categories.*.description' => 'nullable|string|max:255',
            'services' => 'array|max:200',
            'services.*.id' => 'nullable|integer',
            'services.*.name' => 'required|string|max:255',
            'services.*.category_cid' => 'nullable|string|max:20',
            'services.*.duration_minutes' => 'required|integer|min:5|max:600',
            'services.*.price' => 'nullable|string|max:50',
            'services.*.description' => 'nullable|string|max:500',
            'services.*.is_bookable' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($etablissement, $validated) {
            $cidToId = $this->syncCategories($etablissement, $validated['categories'] ?? []);
            $this->syncServices($etablissement, $validated['services'] ?? [], $cidToId);
        });

        return back()->with('success', 'Catégories et prestations mises à jour.');
    }

    /**
     * Upsert les catégories et retourne la map cid (client) → id réel.
     *
     * @return array<string, int>
     */
    private function syncCategories(Establishment $etablissement, array $categories): array
    {
        $existingIds = $etablissement->serviceCategories()->pluck('id')->all();
        $keptIds = [];
        $cidToId = [];

        foreach (array_values($categories) as $i => $c) {
            if (! filled($c['name'] ?? null)) {
                continue;
            }

            $attrs = [
                'name' => trim($c['name']),
                'description' => filled($c['description'] ?? null) ? trim($c['description']) : null,
                'sort_order' => $i,
            ];

            $id = (isset($c['id']) && in_array((int) $c['id'], $existingIds)) ? (int) $c['id'] : null;
            if ($id) {
                $etablissement->serviceCategories()->whereKey($id)->update($attrs);
            } else {
                $id = $etablissement->serviceCategories()->create($attrs)->id;
            }

            $keptIds[] = $id;
            $cidToId[$c['cid']] = $id;
        }

        // Catégories retirées : nullOnDelete remet service_category_id à null.
        $etablissement->serviceCategories()->whereNotIn('id', $keptIds)->delete();

        return $cidToId;
    }

    /**
     * @param  array<string, int>  $cidToId
     */
    private function syncServices(Establishment $etablissement, array $services, array $cidToId): void
    {
        $existingIds = $etablissement->services()->pluck('id')->all();
        $keptIds = [];

        foreach (array_values($services) as $i => $s) {
            if (! filled($s['name'] ?? null)) {
                continue;
            }

            $attrs = [
                'service_category_id' => $cidToId[$s['category_cid'] ?? ''] ?? null,
                'name' => trim($s['name']),
                'description' => filled($s['description'] ?? null) ? trim($s['description']) : null,
                'duration_minutes' => (int) $s['duration_minutes'],
                'price' => filled($s['price'] ?? null) ? trim($s['price']) : null,
                'is_bookable' => (bool) ($s['is_bookable'] ?? false),
                'sort_order' => $i,
            ];

            $id = (isset($s['id']) && in_array((int) $s['id'], $existingIds)) ? (int) $s['id'] : null;
            if ($id) {
                $etablissement->services()->whereKey($id)->update($attrs);
            } else {
                $id = $etablissement->services()->create($attrs)->id;
            }

            $keptIds[] = $id;
        }

        $etablissement->services()->whereNotIn('id', $keptIds)->delete();
    }
}
