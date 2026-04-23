<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\Review;
use Illuminate\Http\Request;

class AvisController extends Controller
{
    private function authorize(Request $request, Establishment $etablissement): void
    {
        if (! $request->user()->establishments()->where('establishment_id', $etablissement->id)->exists()) {
            abort(403);
        }
    }

    public function index(Request $request, Establishment $etablissement)
    {
        $this->authorize($request, $etablissement);
        $avis = $etablissement->reviews()->with('user')->latest()->paginate(20);

        return view('client.avis.index', compact('etablissement', 'avis'));
    }

    public function repondre(Request $request, Establishment $etablissement, Review $avis)
    {
        $this->authorize($request, $etablissement);

        return view('client.avis.repondre', compact('etablissement', 'avis'));
    }

    public function storeReponse(Request $request, Establishment $etablissement, Review $avis)
    {
        $this->authorize($request, $etablissement);

        $request->validate(['reply' => 'required|string|max:5000']);

        $avis->update([
            'reply' => $request->reply,
            'replied_at' => now(),
        ]);

        return redirect()->route('client.etablissement.avis', $etablissement)->with('success', 'Réponse enregistrée.');
    }

    public function mesAvis(Request $request)
    {
        $avis = $request->user()->reviews()->with('establishment')->latest()->paginate(20);

        return view('client.avis.mes-avis', compact('avis'));
    }
}
