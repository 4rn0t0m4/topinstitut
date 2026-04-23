<?php

namespace App\Services;

use App\Models\Establishment;
use Illuminate\Database\Eloquent\Collection;

class GeoSearchService
{
    public function nearby(float $lat, float $lng, float $radiusKm = 10, int $limit = 20): Collection
    {
        return Establishment::active()
            ->nearby($lat, $lng, $radiusKm)
            ->limit($limit)
            ->get();
    }

    public static function distance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        return 6371 * acos(
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lng2) - deg2rad($lng1))
            + sin(deg2rad($lat1)) * sin(deg2rad($lat2))
        );
    }
}
