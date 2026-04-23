<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
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
        $faqs = $etablissement->faqs;

        return view('client.etablissement.faq', compact('etablissement', 'faqs'));
    }

    public function store(Request $request, Establishment $etablissement)
    {
        $this->authorize($request, $etablissement);

        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:2000',
        ]);

        $validated['establishment_id'] = $etablissement->id;
        $validated['sort_order'] = $etablissement->faqs()->max('sort_order') + 1;

        Faq::create($validated);

        return back()->with('success', 'Question ajoutée.');
    }

    public function update(Request $request, Establishment $etablissement, Faq $faq)
    {
        $this->authorize($request, $etablissement);
        abort_unless($faq->establishment_id === $etablissement->id, 403);

        $faq->update($request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:2000',
        ]));

        return back()->with('success', 'Question mise à jour.');
    }

    public function destroy(Request $request, Establishment $etablissement, Faq $faq)
    {
        $this->authorize($request, $etablissement);
        abort_unless($faq->establishment_id === $etablissement->id, 403);

        $faq->delete();

        return back()->with('success', 'Question supprimée.');
    }
}
