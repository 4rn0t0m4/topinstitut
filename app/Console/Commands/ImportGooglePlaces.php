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
        {--limit=10 : Nombre d\'établissements à importer par exécution}
        {--departement= : Forcer un département spécifique}
        {--dry-run : Simuler sans insérer en base}';

    protected $description = 'Importe des instituts de beauté depuis Google Places API';

    private string $apiKey;

    private const FIELD_MASK = 'places.id,places.displayName,places.formattedAddress,places.nationalPhoneNumber,places.internationalPhoneNumber,places.websiteUri,places.googleMapsUri,places.regularOpeningHours,places.rating,places.userRatingCount,places.types,places.location,places.editorialSummary,places.businessStatus,places.addressComponents';

    private const SEARCH_TYPES = [
        'institut de beauté',
        'spa bien-être',
        'esthéticienne',
        'thalassothérapie',
    ];

    public function handle(): int
    {
        $this->apiKey = config('services.google_places.api_key');
        if (! $this->apiKey) {
            $this->error('GOOGLE_PLACES_API_KEY non configurée.');

            return self::FAILURE;
        }

        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');
        $imported = 0;

        $dept = $this->getNextDepartment();
        if (! $dept) {
            $this->info('Tous les départements ont été traités. Reset des progressions.');
            DB::table('google_import_progress')->update(['completed' => false]);
            $dept = $this->getNextDepartment();
        }

        $this->info("Département : {$dept->code} - {$dept->name}");

        $cities = City::where('department_code', $dept->code)
            ->orderByDesc('population')
            ->limit(5)
            ->pluck('name')
            ->toArray();

        if (empty($cities)) {
            $cities = [$dept->name];
        }

        foreach (self::SEARCH_TYPES as $searchType) {
            if ($imported >= $limit) {
                break;
            }

            foreach ($cities as $cityName) {
                if ($imported >= $limit) {
                    break;
                }

                $query = "$searchType $cityName";
                $this->line("  Recherche : $query");

                $places = $this->searchPlaces($query);

                foreach ($places as $place) {
                    if ($imported >= $limit) {
                        break;
                    }

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

                    if (Establishment::where('name', $name)
                        ->where('postal_code', $postalCode)
                        ->exists()) {
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
        }

        DB::table('google_import_progress')->updateOrInsert(
            ['department_code' => $dept->code],
            [
                'completed' => true,
                'total_imported' => DB::raw("total_imported + $imported"),
                'last_run_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->info("$imported établissement(s) importé(s) dans le {$dept->code} - {$dept->name}.");

        return self::SUCCESS;
    }

    private function getNextDepartment(): ?Department
    {
        $forcedDept = $this->option('departement');
        if ($forcedDept) {
            return Department::where('code', $forcedDept)->first();
        }

        $processed = DB::table('google_import_progress')
            ->where('completed', true)
            ->pluck('department_code');

        return Department::whereNotIn('code', $processed)
            ->orderBy('code')
            ->first();
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
