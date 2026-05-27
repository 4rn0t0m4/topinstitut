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

        // Sponsorisé = tout compris (19,90 €) : on ne compte pas son Premium en plus.
        $sponsoriseCount = $sponsorisesActifs->count();
        $premiumSeulCount = max($premiumActifs->count() - $sponsoriseCount, 0);

        $stats = [
            'premium_count' => $premiumActifs->count(),
            'sponsorise_count' => $sponsoriseCount,
            'mrr_premium' => $premiumSeulCount * 9.90,
            'mrr_sponsorise' => $sponsoriseCount * 19.90,
            'expires_count' => $expires->count(),
        ];
        $stats['mrr_total'] = $stats['mrr_premium'] + $stats['mrr_sponsorise'];

        return view('admin.abonnements.index', compact('premiumActifs', 'sponsorisesActifs', 'expires', 'stats'));
    }
}
