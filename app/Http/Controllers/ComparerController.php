<?php

namespace App\Http\Controllers;

use App\Models\Establishment;
use Illuminate\Http\Request;

class ComparerController extends Controller
{
    public function index(Request $request)
    {
        $ids = collect(explode(',', (string) $request->input('ids')))
            ->map(fn ($v) => (int) $v)
            ->filter()
            ->take(3)
            ->values()
            ->all();

        if (count($ids) < 2) {
            return redirect()->route('recherche')->with('success', 'Sélectionnez au moins 2 établissements pour les comparer.');
        }

        $establishments = Establishment::active()
            ->whereIn('id', $ids)
            ->with(['photos', 'schedules', 'cityRelation.department'])
            ->get()
            ->sortBy(fn ($e) => array_search($e->id, $ids))
            ->values();

        if ($establishments->count() < 2) {
            return redirect()->route('recherche')->with('success', 'Pas assez d\'établissements valides à comparer.');
        }

        return view('comparer', compact('establishments'));
    }
}
