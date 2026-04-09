<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Avis;
use App\Models\Etablissement;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'etablissements' => Etablissement::count(),
            'etablissements_en_attente' => Etablissement::where('valide', false)->count(),
            'avis_en_attente' => Avis::where('valide', false)->where('refus', false)->count(),
            'utilisateurs' => User::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
