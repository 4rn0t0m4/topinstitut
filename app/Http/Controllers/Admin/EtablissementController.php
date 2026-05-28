<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEstablishmentRequest;
use App\Http\Requests\Admin\UpdateEstablishmentRequest;
use App\Models\Establishment;
use App\Services\EstablishmentService;
use App\Services\SlugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EtablissementController extends Controller
{
    public function __construct(private EstablishmentService $establishments) {}

    public function index(Request $request)
    {
        $query = Establishment::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('valide')) {
            $query->where('is_active', $request->boolean('valide'));
        }

        return view('admin.etablissements.index', [
            'etablissements' => $query->latest()->paginate(25),
        ]);
    }

    public function show(Establishment $etablissement)
    {
        $etablissement->load(['owners', 'approvedReviews', 'photos']);

        return view('admin.etablissements.show', compact('etablissement'));
    }

    public function create()
    {
        return view('admin.etablissements.edit', ['etablissement' => new Establishment]);
    }

    public function edit(Establishment $etablissement)
    {
        return view('admin.etablissements.edit', compact('etablissement'));
    }

    public function store(StoreEstablishmentRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = SlugService::generate($data['name']);
        $data['is_active'] = true;
        $data['city_id'] = $this->resolveCityId($data);

        Establishment::create($data);

        return redirect()->route('admin.etablissements.index')->with('success', 'Établissement créé.');
    }

    public function update(UpdateEstablishmentRequest $request, Establishment $etablissement)
    {
        $data = $request->validated();
        $data['features'] = $request->input('features', []);
        $data['city_id'] = $this->resolveCityId($data);

        if ($error = $this->establishments->featuredLimitError($data, $etablissement)) {
            return back()->withInput()->withErrors(['featured_until' => $error]);
        }

        $etablissement->update($data);

        return redirect()->route('admin.etablissements.index')->with('success', 'Établissement mis à jour.');
    }

    public function destroy(Establishment $etablissement)
    {
        try {
            $etablissement->delete();
        } catch (\Throwable $e) {
            Log::error('Suppression établissement #'.$etablissement->id.' échouée : '.$e->getMessage());

            return back()->withErrors(['delete' => 'Suppression impossible : '.$e->getMessage()]);
        }

        return redirect()->route('admin.etablissements.index')->with('success', 'Établissement supprimé.');
    }

    public function valider(Establishment $etablissement)
    {
        $etablissement->update(['is_active' => true]);

        return back()->with('success', 'Établissement validé.');
    }

    /** Utilise un city_id explicite ou résout par nom+CP via EstablishmentService. */
    private function resolveCityId(array $data): ?int
    {
        if (! empty($data['city_id'])) {
            return (int) $data['city_id'];
        }

        if (empty($data['city'])) {
            return null;
        }

        return $this->establishments->findCityId($data['city'], $data['postal_code'] ?? null);
    }
}
