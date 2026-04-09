<?php

namespace App\Http\Controllers;

use App\Models\Avis;
use App\Models\Departement;
use App\Models\Etablissement;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        $departements = Cache::remember('departements', 3600, fn () => Departement::orderBy('departement')->get());
        $derniersAvis = Avis::approved()->with(['etablissement', 'user'])->latest()->limit(6)->get();
        $derniersEtablissements = Etablissement::valide()->latest()->limit(6)->get();

        return view('home', compact('departements', 'derniersAvis', 'derniersEtablissements'));
    }
}
