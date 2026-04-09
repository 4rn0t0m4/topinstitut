<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Avis;
use App\Models\Etablissement;
use Illuminate\Http\Request;

class AvisController extends Controller
{
    private function authorize(Request $request, Etablissement $etablissement): void
    {
        if (! $request->user()->etablissements()->where('etablissement_id', $etablissement->id)->exists()) {
            abort(403);
        }
    }

    public function index(Request $request, Etablissement $etablissement)
    {
        $this->authorize($request, $etablissement);
        $avis = $etablissement->avis()->with('user')->latest()->paginate(20);

        return view('client.avis.index', compact('etablissement', 'avis'));
    }

    public function repondre(Request $request, Etablissement $etablissement, Avis $avis)
    {
        $this->authorize($request, $etablissement);

        return view('client.avis.repondre', compact('etablissement', 'avis'));
    }

    public function storeReponse(Request $request, Etablissement $etablissement, Avis $avis)
    {
        $this->authorize($request, $etablissement);

        $request->validate(['reponse' => 'required|string|max:5000']);

        $avis->update([
            'reponse' => $request->reponse,
            'reponse_date' => now(),
        ]);

        return redirect()->route('client.etablissement.avis', $etablissement)->with('success', 'Réponse enregistrée.');
    }

    public function mesAvis(Request $request)
    {
        $avis = $request->user()->avis()->with('etablissement')->latest()->paginate(20);

        return view('client.avis.mes-avis', compact('avis'));
    }
}
