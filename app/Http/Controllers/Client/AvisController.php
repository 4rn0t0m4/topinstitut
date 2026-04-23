<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\Review;
use Illuminate\Http\Request;

class AvisController extends Controller
{
    public function index(Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);
        $avis = $etablissement->reviews()->with('user')->latest()->paginate(20);

        return view('client.avis.index', compact('etablissement', 'avis'));
    }

    public function repondre(Establishment $etablissement, Review $avis)
    {
        $this->authorize('manage', $etablissement);

        return view('client.avis.repondre', compact('etablissement', 'avis'));
    }

    public function storeReponse(Request $request, Establishment $etablissement, Review $avis)
    {
        $this->authorize('manage', $etablissement);

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
