<?php

namespace App\Services;

use App\Models\City;
use App\Models\Establishment;
use App\Models\Schedule;

class EstablishmentService
{
    /**
     * Update basic information for an establishment.
     */
    public function updateBasicInfo(Establishment $establishment, array $data): void
    {
        if (empty($data['city_id']) && !empty($data['city'])) {
            $data['city_id'] = $this->findCityId($data['city'], $data['postal_code'] ?? null);
        }

        $establishment->update($data);
    }

    /**
     * Update presentation (description, pricing, tagline) for an establishment.
     */
    public function updatePresentation(Establishment $establishment, array $data): void
    {
        $establishment->update($data);
    }

    /**
     * Update schedules for an establishment.
     */
    public function updateSchedules(Establishment $establishment, array $horaires): void
    {
        foreach ($horaires as $jour => $data) {
            Schedule::updateOrCreate(
                ['establishment_id' => $establishment->id, 'day_of_week' => $jour],
                [
                    'is_closed' => $data['is_closed'] ?? false,
                    'open_am' => $data['open_am'] ?? null,
                    'close_am' => $data['close_am'] ?? null,
                    'open_pm' => $data['open_pm'] ?? null,
                    'close_pm' => $data['close_pm'] ?? null,
                ]
            );
        }
    }

    /**
     * Update location (latitude, longitude) for an establishment.
     */
    public function updateLocation(Establishment $establishment, array $data): void
    {
        $establishment->update($data);
    }

    /**
     * Find a city ID by name and optionally postal code.
     */
    public function findCityId(string $name, ?string $postalCode = null): ?int
    {
        $city = City::where('name', $name)
            ->when($postalCode, fn ($q) => $q->where('postal_code', $postalCode))
            ->orderByDesc('population')
            ->first();

        return $city?->id;
    }

    /**
     * Get establishments managed by a user.
     */
    public function getByOwner(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return Establishment::whereHas('owners', fn ($q) => $q->where('user_id', $userId))
            ->with(['cityRelation', 'schedules', 'photos'])
            ->orderBy('name')
            ->get();
    }
}
