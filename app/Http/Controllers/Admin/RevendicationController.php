<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Revendication;
use Illuminate\Http\Request;

class RevendicationController extends Controller
{
    public function index()
    {
        $revendications = Revendication::where('statut', 'en_attente')
            ->with(['etablissement', 'user'])
            ->latest()
            ->paginate(25);

        return view('admin.revendications.index', compact('revendications'));
    }

    public function moderer(Request $request, Revendication $revendication)
    {
        $request->validate(['action' => 'required|in:approuver,refuser']);

        if ($request->action === 'approuver') {
            $revendication->update(['statut' => 'approuvee']);

            // Ajouter l'utilisateur comme admin de l'établissement
            $revendication->etablissement->administrateurs()->syncWithoutDetaching([$revendication->user_id]);
        } else {
            $revendication->update(['statut' => 'refusee']);
        }

        return redirect()->route('admin.revendications.index')->with('success', 'Demande traitée.');
    }
}
