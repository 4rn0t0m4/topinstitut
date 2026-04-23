<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Department;
use App\Services\SlugService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportGeoData extends Command
{
    protected $signature = 'geo:import {--departments-only : Import only departments} {--cities-only : Import only cities}';

    protected $description = 'Import departments and cities from geo.api.gouv.fr';

    private const API_BASE = 'https://geo.api.gouv.fr';

    public function handle(): int
    {
        if (! $this->option('cities-only')) {
            $this->importDepartments();
        }

        if (! $this->option('departments-only')) {
            $this->importCities();
        }

        return self::SUCCESS;
    }

    private function importDepartments(): void
    {
        $this->info('Importing departments...');

        $response = Http::get(self::API_BASE.'/departements', [
            'fields' => 'nom,codeRegion',
        ]);

        if ($response->failed()) {
            $this->error('Failed to fetch departments from API.');

            return;
        }

        $regions = $this->fetchRegions();
        $articles = $this->getArticles();
        $count = 0;

        foreach ($response->json() as $dept) {
            $code = $dept['code'];
            $name = $dept['nom'];
            $regionName = $regions[$dept['codeRegion']] ?? '';

            Department::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'slug' => SlugService::generate($name),
                    'region' => $regionName,
                    'article' => $articles[$code] ?? 'du ',
                ]
            );
            $count++;
        }

        $this->info("  {$count} departments imported.");
    }

    private function importCities(): void
    {
        $this->info('Importing cities...');

        $departments = Department::pluck('code');
        $totalCount = 0;

        $bar = $this->output->createProgressBar($departments->count());
        $bar->start();

        foreach ($departments as $deptCode) {
            $response = Http::get(self::API_BASE.'/departements/'.$deptCode.'/communes', [
                'fields' => 'nom,codesPostaux,centre,population,code',
            ]);

            if ($response->failed()) {
                $this->warn("  Failed to fetch cities for department {$deptCode}");
                $bar->advance();

                continue;
            }

            $cities = $response->json();
            $batch = [];

            foreach ($cities as $commune) {
                $postalCode = $commune['codesPostaux'][0] ?? substr($commune['code'], 0, 5);
                $lat = $commune['centre']['coordinates'][1] ?? null;
                $lng = $commune['centre']['coordinates'][0] ?? null;
                $name = $commune['nom'];
                $inseeCode = $commune['code'];

                $slug = SlugService::generate($name);

                // Ensure unique slug by appending postal code if needed
                $existingSlug = City::where('slug', $slug)->where('insee_code', '!=', $inseeCode)->exists();
                if ($existingSlug) {
                    $slug = $slug.'-'.$postalCode;
                }

                $batch[] = [
                    'name' => $name,
                    'slug' => $slug,
                    'postal_code' => substr($postalCode, 0, 5),
                    'insee_code' => $inseeCode,
                    'department_code' => $deptCode,
                    'population' => $commune['population'] ?? 0,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Upsert in chunks
            foreach (array_chunk($batch, 500) as $chunk) {
                City::upsert($chunk, ['insee_code'], ['name', 'slug', 'postal_code', 'population', 'latitude', 'longitude', 'updated_at']);
            }

            $totalCount += count($batch);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("  {$totalCount} cities imported.");
    }

    private function fetchRegions(): array
    {
        $response = Http::get(self::API_BASE.'/regions');
        if ($response->failed()) {
            return [];
        }

        $regions = [];
        foreach ($response->json() as $region) {
            $regions[$region['code']] = $region['nom'];
        }

        return $regions;
    }

    private function getArticles(): array
    {
        return [
            '01' => "de l'", '02' => "de l'", '03' => "de l'", '04' => 'des ',
            '05' => 'des ', '06' => 'des ', '07' => "de l'", '08' => 'des ',
            '09' => "de l'", '10' => "de l'", '11' => "de l'", '12' => "de l'",
            '13' => 'des ', '14' => 'du ', '15' => 'du ', '16' => 'de la ',
            '17' => 'de la ', '18' => 'du ', '19' => 'de la ', '2A' => 'de ',
            '2B' => 'de ', '21' => 'de la ', '22' => 'des ', '23' => 'de la ',
            '24' => 'de la ', '25' => 'du ', '26' => 'de la ', '27' => "de l'",
            '28' => "d'", '29' => 'du ', '30' => 'du ', '31' => 'de la ',
            '32' => 'du ', '33' => 'de la ', '34' => "de l'", '35' => "d'",
            '36' => "de l'", '37' => "d'", '38' => "de l'", '39' => 'du ',
            '40' => 'des ', '41' => 'du ', '42' => 'de la ', '43' => 'de la ',
            '44' => 'de la ', '45' => 'du ', '46' => 'du ', '47' => 'du ',
            '48' => 'de la ', '49' => 'du ', '50' => 'de la ', '51' => 'de la ',
            '52' => 'de la ', '53' => 'de la ', '54' => 'de ', '55' => 'de la ',
            '56' => 'du ', '57' => 'de la ', '58' => 'de la ', '59' => 'du ',
            '60' => "de l'", '61' => "de l'", '62' => 'du ', '63' => 'du ',
            '64' => 'des ', '65' => 'des ', '66' => 'des ', '67' => 'du ',
            '68' => 'du ', '69' => 'du ', '70' => 'de la ', '71' => 'de ',
            '72' => 'de la ', '73' => 'de la ', '74' => 'de la ', '75' => 'de ',
            '76' => 'de la ', '77' => 'de ', '78' => 'des ', '79' => 'des ',
            '80' => 'de la ', '81' => 'du ', '82' => 'du ', '83' => 'du ',
            '84' => 'du ', '85' => 'de la ', '86' => 'de la ', '87' => 'de la ',
            '88' => 'des ', '89' => "de l'", '90' => 'du ', '91' => "de l'",
            '92' => 'des ', '93' => 'de la ', '94' => 'du ', '95' => 'du ',
            '971' => 'de la ', '972' => 'de la ', '973' => 'de la ',
            '974' => 'de la ', '976' => 'de ',
        ];
    }
}
