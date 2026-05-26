<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Establishment;

class AbonnementController extends Controller
{
    public function index()
    {
        $now = now();

        $premiumActifs = Establishment::where('subscription_tier', 'premium')
            ->where(fn ($q) => $q->whereNull('subscription_ends_at')->orWhere('subscription_ends_at', '>', $now))
            ->with('owners')
            ->orderByDesc('subscription_ends_at')
            ->get();

        $sponsorisesActifs = Establishment::whereNotNull('featured_until')
            ->where('featured_until', '>', $now)
            ->with('owners')
            ->orderBy('featured_until')
            ->get();

        $expires = Establishment::where('subscription_tier', 'premium')
            ->whereNotNull('subscription_ends_at')
            ->where('subscription_ends_at', '<=', $now)
            ->with('owners')
            ->orderByDesc('subscription_ends_at')
            ->limit(50)
            ->get();

        $stats = [
            'premium_count' => $premiumActifs->count(),
            'sponsorise_count' => $sponsorisesActifs->count(),
            'mrr_premium' => $premiumActifs->count() * 9.90,
            'mrr_sponsorise' => $sponsorisesActifs->count() * 20.00,
            'expires_count' => $expires->count(),
        ];
        $stats['mrr_total'] = $stats['mrr_premium'] + $stats['mrr_sponsorise'];

        return view('admin.abonnements.index', compact('premiumActifs', 'sponsorisesActifs', 'expires', 'stats'));
    }
}
