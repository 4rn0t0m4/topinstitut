<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $etablissements = $user->establishments()->withCount('reviews')->get();

        $stats = [
            'establishments' => $etablissements->count(),
            'reviews_published' => $user->reviews()->count(),
            'reviews_received' => $etablissements->sum('reviews_count'),
            'favorites' => $user->favorites()->count(),
        ];

        return view('client.dashboard', compact('user', 'etablissements', 'stats'));
    }
}
