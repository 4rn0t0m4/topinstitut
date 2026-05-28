<?php

namespace App\Services;

use App\Models\City;
use App\Models\Establishment;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;

class EstablishmentService
{
    /**
     * Upsert le catalogue catégories + prestations d'un établissement en une
     * transaction. Les cid (client) côté formulaire permettent de rattacher
     * les nouvelles prestations à de nouvelles catégories non encore persistées.
     *
     * @param  array<int, array{cid:string, id?:int|null, name:string, description?:?string}>  $categories
     * @param  array<int, array{id?:int|null, name:string, category_cid?:?string, duration_minutes:int|string, price?:?string, description?:?string, is_bookable?:bool}>  $services
     */
    public function syncServiceCatalog(Establishment $establishment, array $categories, array $services): void
    {
        // Vérifie en amont que chaque prestation pointe vers une catégorie présente
        // dans le payload (sinon = catégorie supprimée mais prestation conservée).
        $submittedCids = collect($categories)->pluck('cid')->filter()->all();
        foreach ($services as $i => $svc) {
            $cid = $svc['category_cid'] ?? null;
            if (! $cid || ! in_array($cid, $submittedCids, true)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "services.$i.category_cid" => 'Cette prestation n\'est rattachée à aucune catégorie existante.',
                ]);
            }
        }

        DB::transaction(function () use ($establishment, $categories, $services) {
            $cidToId = $this->syncCategories($establishment, $categories);
            $this->syncServices($establishment, $services, $cidToId);
        });
    }

    /**
     * @return array<string, int>  cid (client) → id réel
     */
    private function syncCategories(Establishment $establishment, array $categories): array
    {
        $existingIds = $establishment->serviceCategories()->pluck('id')->all();
        $keptIds = [];
        $cidToId = [];

        foreach (array_values($categories) as $i => $c) {
            if (! filled($c['name'] ?? null)) {
                continue;
            }

            $attrs = [
                'name' => trim($c['name']),
                'description' => filled($c['description'] ?? null) ? trim($c['description']) : null,
                'sort_order' => $i,
            ];

            $id = (isset($c['id']) && in_array((int) $c['id'], $existingIds)) ? (int) $c['id'] : null;
            if ($id) {
                $establishment->serviceCategories()->whereKey($id)->update($attrs);
            } else {
                $id = $establishment->serviceCategories()->create($attrs)->id;
            }

            $keptIds[] = $id;
            $cidToId[$c['cid']] = $id;
        }

        // Catégories retirées : la FK nullOnDelete remet service_category_id à null
        // sur les prestations rattachées (elles deviennent « Sans catégorie »).
        $establishment->serviceCategories()->whereNotIn('id', $keptIds)->delete();

        return $cidToId;
    }

    /**
     * @param  array<string, int>  $cidToId
     */
    private function syncServices(Establishment $establishment, array $services, array $cidToId): void
    {
        $existingIds = $establishment->services()->pluck('id')->all();
        $keptIds = [];

        foreach (array_values($services) as $i => $s) {
            if (! filled($s['name'] ?? null)) {
                continue;
            }

            $attrs = [
                'service_category_id' => $cidToId[$s['category_cid'] ?? ''] ?? null,
                'name' => trim($s['name']),
                'description' => filled($s['description'] ?? null) ? trim($s['description']) : null,
                'duration_minutes' => (int) $s['duration_minutes'],
                'price' => filled($s['price'] ?? null) ? trim($s['price']) : null,
                'is_bookable' => (bool) ($s['is_bookable'] ?? false),
                'sort_order' => $i,
            ];

            $id = (isset($s['id']) && in_array((int) $s['id'], $existingIds)) ? (int) $s['id'] : null;
            if ($id) {
                $establishment->services()->whereKey($id)->update($attrs);
            } else {
                $id = $establishment->services()->create($attrs)->id;
            }

            $keptIds[] = $id;
        }

        $establishment->services()->whereNotIn('id', $keptIds)->delete();
    }

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
     * Limite : 5 sponsorisés actifs max par département. Retourne un message
     * d'erreur si la mise à jour dépasse la limite, sinon null.
     *
     * @param  array{featured_until?:?string, city_id?:?int, department_code?:?string}  $data
     */
    public function featuredLimitError(array $data, ?Establishment $current = null): ?string
    {
        if (empty($data['featured_until']) || ! \Illuminate\Support\Carbon::parse($data['featured_until'])->isFuture()) {
            return null;
        }

        $deptCode = $this->resolveDepartmentCode($data, $current);
        if (! $deptCode) {
            return null;
        }

        $cityIds = City::where('department_code', $deptCode)->pluck('id');

        $count = Establishment::query()
            ->when($current, fn ($q) => $q->where('id', '!=', $current->id))
            ->whereNotNull('featured_until')
            ->where('featured_until', '>', now())
            ->where(fn ($q) => $q->where('department_code', $deptCode)->orWhereIn('city_id', $cityIds))
            ->count();

        return $count >= 5
            ? 'Limite de 5 sponsorisés actifs atteinte pour ce département. Retirez-en un avant d\'en ajouter un nouveau.'
            : null;
    }

    private function resolveDepartmentCode(array $data, ?Establishment $current): ?string
    {
        if (! empty($data['city_id'])) {
            $code = City::where('id', $data['city_id'])->value('department_code');
            if ($code) {
                return $code;
            }
        }

        return $data['department_code'] ?? $current?->department_code;
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
