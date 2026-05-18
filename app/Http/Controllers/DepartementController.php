<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Establishment;

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

        $now = now();
        $cityIds = $department->cities()->pluck('id');
        $premiums = Establishment::active()
            ->where(fn ($q) => $q->where('department_code', $department->code)
                ->orWhereIn('city_id', $cityIds))
            ->where('subscription_tier', 'premium')
            ->where(fn ($q) => $q->whereNull('subscription_ends_at')->orWhere('subscription_ends_at', '>', $now))
            ->with(['schedules', 'photos'])
            ->orderByRaw('CASE WHEN featured_until IS NOT NULL AND featured_until > ? THEN 0 ELSE 1 END', [$now])
            ->orderByDesc('rating')
            ->orderByDesc('review_count')
            ->limit(12)
            ->get();

        $markers = Establishment::active()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where(fn ($q) => $q->where('department_code', $department->code)
                ->orWhereIn('city_id', $cityIds))
            ->get()
            ->map(fn ($e) => [
                'lat' => (float) $e->latitude,
                'lng' => (float) $e->longitude,
                'title' => $e->name,
                'type' => $e->type_label,
                'city' => $e->city,
                'postal_code' => $e->postal_code,
                'rating' => $e->review_count > 0 ? round($e->rating, 1) : null,
                'url' => $e->url,
            ])
            ->values();

        return view('departement.show', compact('department', 'cities', 'markers', 'premiums'));
    }
}
