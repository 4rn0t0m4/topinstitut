<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Department;
use App\Models\Establishment;

class VilleController extends Controller
{
    public function show(string $deptSlug, string $citySlug)
    {
        $department = Department::where('slug', $deptSlug)->firstOrFail();
        $city = City::where('slug', $citySlug)
            ->where('department_code', $department->code)
            ->firstOrFail();

        // Tri : featured > premium > city_rank > rating
        $now = now();
        $query = Establishment::active()->where('city_id', $city->id)
            ->orderByRaw('CASE WHEN featured_until IS NOT NULL AND featured_until > ? THEN 0 ELSE 1 END', [$now])
            ->orderByRaw("CASE WHEN subscription_tier = 'premium' AND (subscription_ends_at IS NULL OR subscription_ends_at > ?) THEN 0 ELSE 1 END", [$now])
            ->orderByRaw('city_rank = 0 ASC, city_rank ASC, rating DESC');

        if ($query->count() === 0 && $city->latitude && $city->longitude) {
            $query = Establishment::active()->nearby($city->latitude, $city->longitude, 15);
        }

        $establishments = $query->with(['schedules', 'photos'])->paginate(20);

        return view('ville.show', compact('city', 'department', 'establishments'));
    }
}
