<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\RatingService;
use Illuminate\Http\Request;

class AvisController extends Controller
{
    public function index()
    {
        $avis = Review::where('is_approved', false)
            ->where('is_rejected', false)
            ->with(['establishment', 'user'])
            ->latest()
            ->paginate(25);

        return view('admin.avis.index', compact('avis'));
    }

    public function show(Review $avis)
    {
        $avis->load(['establishment', 'user']);

        return view('admin.avis.show', compact('avis'));
    }

    public function moderer(Request $request, Review $avis, RatingService $ratingService)
    {
        $request->validate([
            'action' => 'required|in:valider,refuser',
        ]);

        if ($request->action === 'valider') {
            $avis->update(['is_approved' => true, 'is_rejected' => false]);
            $avis->user?->increment('review_count');
        } else {
            $avis->update(['is_approved' => false, 'is_rejected' => true]);
        }

        $ratingService->recalculate($avis->establishment);

        return redirect()->route('admin.avis.index')->with('success', 'Avis modéré.');
    }
}
