<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guide;
use App\Services\SlugService;
use Illuminate\Http\Request;

class GuideController extends Controller
{
    public function index()
    {
        $guides = Guide::latest()->paginate(20);

        return view('admin.guides.index', compact('guides'));
    }

    public function create()
    {
        return view('admin.guides.edit', ['guide' => new Guide]);
    }

    public function store(Request $request)
    {
        $data = $this->validateGuide($request);
        $data['slug'] = SlugService::generate($data['title']);

        $i = 1;
        $base = $data['slug'];
        while (Guide::where('slug', $data['slug'])->exists()) {
            $data['slug'] = "$base-$i";
            $i++;
        }

        if ($data['is_published'] && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        Guide::create($data);

        return redirect()->route('admin.guides.index')->with('success', 'Guide créé.');
    }

    public function edit(Guide $guide)
    {
        return view('admin.guides.edit', compact('guide'));
    }

    public function update(Request $request, Guide $guide)
    {
        $data = $this->validateGuide($request);

        if ($data['is_published'] && empty($guide->published_at)) {
            $data['published_at'] = now();
        }

        $guide->update($data);

        return redirect()->route('admin.guides.index')->with('success', 'Guide mis à jour.');
    }

    public function destroy(Guide $guide)
    {
        $guide->delete();

        return redirect()->route('admin.guides.index')->with('success', 'Guide supprimé.');
    }

    private function validateGuide(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'body' => 'required|string',
            'cover_image' => 'nullable|string|max:500',
            'author' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:160',
            'is_published' => 'boolean',
        ]);
    }
}
