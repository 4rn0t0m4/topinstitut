<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\Review;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'etablissements' => Establishment::count(),
            'etablissements_en_attente' => Establishment::where('is_active', false)->count(),
            'avis_en_attente' => Review::where('is_approved', false)->where('is_rejected', false)->count(),
            'utilisateurs' => User::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
