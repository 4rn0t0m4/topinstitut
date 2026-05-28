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
            'city_id' => 'nullable|integer|exists:cities,id',
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
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $validated['features'] = $request->input('features', []);
        $validated['city_id'] = $this->resolveCityId($validated);

        if ($error = $this->checkFeaturedLimit($validated, $etablissement)) {
            return back()->withInput()->withErrors(['featured_until' => $error]);
        }

        $etablissement->update($validated);

        return redirect()->route('admin.etablissements.index')->with('success', 'Établissement mis à jour.');
    }

    /**
     * Limite : 5 sponsorisés actifs maximum par département.
     * Retourne un message d'erreur si la limite serait dépassée, sinon null.
     */
    private function checkFeaturedLimit(array $data, ?Establishment $current = null): ?string
    {
        if (empty($data['featured_until']) || ! \Illuminate\Support\Carbon::parse($data['featured_until'])->isFuture()) {
            return null;
        }

        $deptCode = $this->resolveDepartmentCode($data, $current);
        if (! $deptCode) {
            return null;
        }

        $cityIds = \App\Models\City::where('department_code', $deptCode)->pluck('id');

        $count = Establishment::query()
            ->when($current, fn ($q) => $q->where('id', '!=', $current->id))
            ->whereNotNull('featured_until')
            ->where('featured_until', '>', now())
            ->where(fn ($q) => $q->where('department_code', $deptCode)->orWhereIn('city_id', $cityIds))
            ->count();

        return $count >= 5
            ? 'Limite de 5 sponsorisés actifs atteinte pour ce département. Retirez-en un avant d\'en ajouter un nouveau.'
            : null;
    }

    private function resolveDepartmentCode(array $data, ?Establishment $current): ?string
    {
        if (! empty($data['city_id'])) {
            $code = \App\Models\City::where('id', $data['city_id'])->value('department_code');
            if ($code) {
                return $code;
            }
        }

        return $data['department_code'] ?? $current?->department_code;
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
            'city_id' => 'nullable|integer|exists:cities,id',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = SlugService::generate($validated['name']);
        $validated['is_active'] = true;
        $validated['city_id'] = $this->resolveCityId($validated);

        Establishment::create($validated);

        return redirect()->route('admin.etablissements.index')->with('success', 'Établissement créé.');
    }

    private function resolveCityId(array $data): ?int
    {
        if (! empty($data['city_id'])) {
            return (int) $data['city_id'];
        }
        if (empty($data['city'])) {
            return null;
        }
        $match = \App\Models\City::where('name', $data['city'])
            ->when($data['postal_code'] ?? null, fn ($q, $cp) => $q->where('postal_code', $cp))
            ->orderByDesc('population')
            ->first();
        return $match?->id;
    }

    public function destroy(Establishment $etablissement)
    {
        try {
            $etablissement->delete();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Suppression établissement #'.$etablissement->id.' échouée : '.$e->getMessage());

            return back()->withErrors(['delete' => 'Suppression impossible : '.$e->getMessage()]);
        }

        return redirect()->route('admin.etablissements.index')->with('success', 'Établissement supprimé.');
    }

    public function valider(Establishment $etablissement)
    {
        $etablissement->update(['is_active' => true]);

        return back()->with('success', 'Établissement validé.');
    }
}
