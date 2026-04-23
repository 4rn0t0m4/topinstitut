<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use Illuminate\Http\Request;

class RevendicationController extends Controller
{
    public function index()
    {
        $revendications = Claim::where('status', 'pending')
            ->with(['establishment', 'user'])
            ->latest()
            ->paginate(25);

        return view('admin.revendications.index', compact('revendications'));
    }

    public function moderer(Request $request, Claim $revendication)
    {
        $request->validate(['action' => 'required|in:approuver,refuser']);

        if ($request->action === 'approuver') {
            $revendication->update(['status' => 'approved']);

            // Ajouter l'utilisateur comme admin de l'établissement
            $revendication->establishment->owners()->syncWithoutDetaching([$revendication->user_id]);
        } else {
            $revendication->update(['status' => 'rejected']);
        }

        return redirect()->route('admin.revendications.index')->with('success', 'Demande traitée.');
    }
}
