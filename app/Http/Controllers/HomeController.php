<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Establishment;
use App\Models\Review;

class HomeController extends Controller
{
    public function index()
    {
        $departments = Department::orderBy('code')->get();
        $latestReviews = Review::approved()->with(['establishment', 'user'])->latest()->limit(6)->get();
        $latestEstablishments = Establishment::active()->has('photos')->with(['schedules', 'photos'])->latest()->limit(6)->get();

        return view('home', compact('departments', 'latestReviews', 'latestEstablishments'));
    }
}
