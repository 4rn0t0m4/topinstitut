<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);

        return view('client.etablissement.faq', [
            'etablissement' => $etablissement,
            'faqs' => $etablissement->faqs,
        ]);
    }

    public function store(Request $request, Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);

        $etablissement->faqs()->create(array_merge(
            $request->validate(['question' => 'required|string|max:255', 'answer' => 'required|string|max:2000']),
            ['sort_order' => $etablissement->faqs()->max('sort_order') + 1]
        ));

        return back()->with('success', 'Question ajoutée.');
    }

    public function update(Request $request, Establishment $etablissement, Faq $faq)
    {
        $this->authorize('manage', $etablissement);

        $faq->update($request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:2000',
        ]));

        return back()->with('success', 'Question mise à jour.');
    }

    public function destroy(Establishment $etablissement, Faq $faq)
    {
        $this->authorize('manage', $etablissement);

        $faq->delete();

        return back()->with('success', 'Question supprimée.');
    }
}
