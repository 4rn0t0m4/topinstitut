<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $etablissements = $user->etablissements()->withCount('avis')->get();

        return view('client.dashboard', compact('user', 'etablissements'));
    }
}
