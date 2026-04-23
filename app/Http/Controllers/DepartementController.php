<?php

namespace App\Http\Controllers;

use App\Models\Department;

class DepartementController extends Controller
{
    public function show(string $slug)
    {
        $department = Department::where('slug', $slug)->firstOrFail();
        $cities = $department->cities()
            ->whereHas('establishments', fn ($q) => $q->where('is_active', true))
            ->withCount(['establishments' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get();

        return view('departement.show', compact('department', 'cities'));
    }
}
