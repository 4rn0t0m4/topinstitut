<?php

namespace App\Http\Controllers;

use App\Models\Guide;

class GuideController extends Controller
{
    public function index()
    {
        $guides = Guide::published()
            ->latest('published_at')
            ->latest('created_at')
            ->paginate(12);

        return view('guides.index', compact('guides'));
    }

    public function show(string $slug)
    {
        $guide = Guide::published()->where('slug', $slug)->firstOrFail();

        $related = Guide::published()
            ->where('id', '!=', $guide->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('guides.show', compact('guide', 'related'));
    }
}
