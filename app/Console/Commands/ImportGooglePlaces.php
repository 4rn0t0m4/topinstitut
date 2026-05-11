<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Department;
use App\Models\Establishment;
use App\Services\SlugService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ImportGooglePlaces extends Command
{
    protected $signature = 'import:google-places
        {--cities=5 : Nombre de villes (par population) à interroger dans le département}
        {--departement= : Forcer un département spécifique (bypass cursor)}
        {--query= : Forcer un index de requête (0..N-1) — utilisé avec --departement}
        {--dry-run : Simuler sans insérer en base}';

    protected $description = 'Importe des instituts de beauté depuis Google Places API';

    private string $apiKey;

    private const FIELD_MASK = 'places.id,places.displayName,places.formattedAddress,places.nationalPhoneNumber,places.internationalPhoneNumber,places.websiteUri,places.googleMapsUri,places.regularOpeningHours,places.rating,places.userRatingCount,places.types,places.location,places.editorialSummary,places.businessStatus,places.addressComponents';

    private const SEARCH_TYPES = [
        'institut de beauté',
        'spa bien-être',
        'esthéticienne',
    ];

    public function handle(): int
    {
        $this->apiKey = config('services.google_places.api_key');
        if (! $this->apiKey) {
            $this->error('GOOGLE_PLACES_API_KEY non configurée.');

            return self::FAILURE;
        }

        $dryRun = $this->option('dry-run');
        $citiesPerDept = (int) $this->option('cities');

        [$dept, $queryIndex, $cycleCount] = $this->advanceCursor();
        if (! $dept) {
            $this->error('Aucun département en base.');

            return self::FAILURE;
        }

        // Safety : si query_index dépasse le tableau (suite à la suppression
        // d'un type de recherche), on retombe dans les bornes.
        $queryIndex = $queryIndex % count(self::SEARCH_TYPES);
        $searchType = self::SEARCH_TYPES[$queryIndex];
        $cityOffset = $cycleCount * $citiesPerDept;
        $this->info("Département : {$dept->code} - {$dept->name}");
        $this->info("Requête (#".($queryIndex + 1).'/'.count(self::SEARCH_TYPES)."): {$searchType}");
        $this->info("Cycle #{$cycleCount} — villes #".($cityOffset + 1).' à #'.($cityOffset + $citiesPerDept));

        $cities = City::where('department_code', $dept->code)
            ->orderByDesc('population')
            ->offset($cityOffset)
            ->limit($citiesPerDept)
            ->pluck('name')
            ->toArray();

        if (empty($cities)) {
            $this->warn("  Aucune ville à cette profondeur pour ce département — skip.");

            return self::SUCCESS;
        }

        $imported = 0;
        foreach ($cities as $cityName) {
            $query = "$searchType $cityName";
            $this->line("  Recherche : $query");
            $places = $this->searchPlaces($query);

            foreach ($places as $place) {
                $placeId = $place['id'] ?? '';
                if (! $placeId) {
                    continue;
                }

                if (DB::table('google_imports')->where('place_id', $placeId)->exists()) {
                    continue;
                }

                $name = $place['displayName']['text'] ?? '';
                $postalCode = $this->extractComponent($place, 'postal_code');
                $cityFound = $this->extractComponent($place, 'locality');

                if (Establishment::where('name', $name)->where('postal_code', $postalCode)->exists()) {
                    DB::table('google_imports')->insert([
                        'place_id' => $placeId,
                        'status' => 'duplicate',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $this->line("    DOUBLON : $name ($postalCode)");

                    continue;
                }

                if ($dryRun) {
                    $this->info("    [DRY-RUN] $name - $cityFound ($postalCode)");
                    $imported++;

                    continue;
                }

                $establishmentId = $this->createEstablishment($place);

                if ($establishmentId) {
                    DB::table('google_imports')->insert([
                        'place_id' => $placeId,
                        'establishment_id' => $establishmentId,
                        'status' => 'imported',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $imported++;
                    $this->info("    IMPORTÉ : $name - $cityFound ($postalCode)");
                }
            }
        }

        $this->info("$imported établissement(s) importé(s) dans le {$dept->code} - {$dept->name}.");

        return self::SUCCESS;
    }

    /**
     * Advance the cursor one step: next department for the current query.
     * When all departments are done, advance to the next query and restart the department loop.
     * cycle_count increments at each full wrap (4 queries × all depts) — used as city offset.
     *
     * @return array{0: ?Department, 1: int, 2: int}  [department, query_index, cycle_count]
     */
    private function advanceCursor(): array
    {
        // Override via CLI
        if ($forced = $this->option('departement')) {
            $dept = Department::where('code', $forced)->first();
            $cursor = DB::table('google_import_cursor')->where('id', 1)->first();
            $queryIndex = $this->option('query') !== null
                ? (int) $this->option('query')
                : (int) ($cursor->query_index ?? 0);

            return [$dept, $queryIndex, (int) ($cursor->cycle_count ?? 0)];
        }

        $cursor = DB::table('google_import_cursor')->where('id', 1)->first();
        if (! $cursor) {
            DB::table('google_import_cursor')->insert(['id' => 1, 'query_index' => 0, 'cycle_count' => 0, 'created_at' => now(), 'updated_at' => now()]);
            $cursor = (object) ['query_index' => 0, 'last_department_code' => null, 'cycle_count' => 0];
        }

        $queryIndex = (int) $cursor->query_index;
        $cycleCount = (int) $cursor->cycle_count;
        $lastCode = $cursor->last_department_code;

        $dept = Department::when($lastCode, fn ($q) => $q->where('code', '>', $lastCode))
            ->orderBy('code')
            ->first();

        if (! $dept) {
            // End of departments for this query → next query
            $oldQueryIndex = $queryIndex;
            $queryIndex = ($queryIndex + 1) % count(self::SEARCH_TYPES);
            $dept = Department::orderBy('code')->first();

            // Tout wrap (y compris quand count(SEARCH_TYPES) a diminué et que l'ancien
            // query_index sauvegardé dépassait la nouvelle taille) incrémente cycle_count.
            if ($queryIndex <= $oldQueryIndex) {
                $cycleCount++;
            }

            DB::table('google_import_cursor')->where('id', 1)->update([
                'query_index' => $queryIndex,
                'last_department_code' => $dept?->code,
                'cycle_count' => $cycleCount,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('google_import_cursor')->where('id', 1)->update([
                'last_department_code' => $dept->code,
                'updated_at' => now(),
            ]);
        }

        return [$dept, $queryIndex, $cycleCount];
    }

    private function searchPlaces(string $query): array
    {
        $response = Http::withHeaders([
            'X-Goog-Api-Key' => $this->apiKey,
            'X-Goog-FieldMask' => self::FIELD_MASK,
        ])->post('https://places.googleapis.com/v1/places:searchText', [
            'textQuery' => $query,
            'languageCode' => 'fr',
            'maxResultCount' => 20,
        ]);

        if (! $response->ok()) {
            $this->warn('    Erreur API : ' . $response->json('error.message', 'Inconnue'));

            return [];
        }

        return $response->json('places', []);
    }

    private function createEstablishment(array $place): ?int
    {
        $name = $place['displayName']['text'] ?? '';
        $location = $place['location'] ?? [];
        $hours = $place['regularOpeningHours'] ?? [];
        $types = $place['types'] ?? [];

        $type = 0;
        if (in_array('spa', $types) || in_array('massage_spa', $types)) {
            $type = 2;
        }

        $postalCode = $this->extractComponent($place, 'postal_code');
        $cityName = $this->extractComponent($place, 'locality');
        $route = $this->extractComponent($place, 'route');
        $streetNumber = $this->extractComponent($place, 'street_number');

        $address = trim(($streetNumber ? "$streetNumber " : '') . $route);

        $city = null;
        if ($postalCode) {
            $city = City::where('postal_code', $postalCode)
                ->orderByRaw("CASE WHEN name LIKE ? THEN 0 ELSE 1 END", [$cityName . '%'])
                ->first();
        }

        $deptCode = $city?->department_code;
        if (! $deptCode && $postalCode) {
            $deptCode = substr($postalCode, 0, 2);
        }

        $slug = SlugService::generate($name . ' ' . $cityName . ' ' . $postalCode);
        $baseSlug = $slug;
        $i = 1;
        while (Establishment::where('slug', $slug)->exists()) {
            $slug = "$baseSlug-$i";
            $i++;
        }

        $phone = $place['nationalPhoneNumber'] ?? '';
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        $description = $place['editorialSummary']['text'] ?? '';

        $establishmentId = DB::table('establishments')->insertGetId([
            'type' => $type,
            'name' => $name,
            'slug' => $slug,
            'google_place_id' => $place['id'] ?? null,
            'email' => null,
            'website' => $place['websiteUri'] ?? null,
            'google_maps_url' => $place['googleMapsUri'] ?? null,
            'address' => $address ?: null,
            'postal_code' => $postalCode ?: null,
            'city' => $cityName ?: null,
            'department_code' => $deptCode ?: null,
            'city_id' => $city?->id,
            'latitude' => $location['latitude'] ?? null,
            'longitude' => $location['longitude'] ?? null,
            'radius' => 0,
            'description' => $description ?: null,
            'phone' => substr($phone, 0, 20) ?: null,
            'is_active' => true,
            'rating' => 0,
            'review_count' => 0,
            'google_rating' => $place['rating'] ?? null,
            'google_review_count' => $place['userRatingCount'] ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (! empty($hours['periods'])) {
            $this->importSchedules($establishmentId, $hours['periods']);
        }

        return $establishmentId;
    }

    private function importSchedules(int $establishmentId, array $periods): void
    {
        $grouped = [];
        foreach ($periods as $period) {
            $day = $period['open']['day'] ?? null;
            if ($day === null) {
                continue;
            }
            $dayOfWeek = $day === 0 ? 7 : $day;
            $grouped[$dayOfWeek][] = $period;
        }

        $format = fn ($p, $key) => str_pad($p[$key]['hour'] ?? 0, 2, '0', STR_PAD_LEFT)
            . ':' . str_pad($p[$key]['minute'] ?? 0, 2, '0', STR_PAD_LEFT) . ':00';

        foreach ($grouped as $dayOfWeek => $dayPeriods) {
            usort($dayPeriods, fn ($a, $b) => ($a['open']['hour'] ?? 0) - ($b['open']['hour'] ?? 0));

            $first = $dayPeriods[0];
            $second = $dayPeriods[1] ?? null;

            DB::table('schedules')->insertOrIgnore([
                'establishment_id' => $establishmentId,
                'day_of_week' => $dayOfWeek,
                'open_am' => $format($first, 'open'),
                'close_am' => $format($first, 'close'),
                'open_pm' => $second ? $format($second, 'open') : null,
                'close_pm' => $second ? $format($second, 'close') : null,
                'is_closed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function extractComponent(array $place, string $type): string
    {
        foreach ($place['addressComponents'] ?? [] as $comp) {
            if (in_array($type, $comp['types'] ?? [])) {
                return $comp['longText'] ?? ($comp['shortText'] ?? '');
            }
        }

        return '';
    }
}
