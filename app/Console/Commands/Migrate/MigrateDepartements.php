<?php

namespace App\Console\Commands\Migrate;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateDepartements extends Command
{
    protected $signature = 'migrate:legacy-departements';

    protected $description = 'Migre les départements depuis la base legacy';

    public function handle(): int
    {
        $this->info('Migration des départements...');

        $rows = DB::connection('legacy')->table('departement')->get();

        $count = 0;
        foreach ($rows as $row) {
            DB::table('departements')->updateOrInsert(
                ['numero' => $row->numero],
                [
                    'departement' => $this->convert($row->departement),
                    'departement_url' => $row->departement_url,
                    'region' => $this->convert($row->region),
                    'article' => $this->convert($row->article) ?: null,
                    'gmap_latitude' => $row->gmap_latitude ?: null,
                    'gmap_longitude' => $row->gmap_longitude ?: null,
                    'zoom' => $row->zoom ?: 9,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $count++;
        }

        // Ajouter les départements DOM-TOM manquants dans le legacy
        $domTom = [
            ['numero' => '97', 'departement' => 'Outre-Mer', 'departement_url' => 'Outre-Mer', 'region' => 'DOM-TOM', 'article' => 'en '],
        ];
        foreach ($domTom as $dept) {
            if (! DB::table('departements')->where('numero', $dept['numero'])->exists()) {
                DB::table('departements')->insert(array_merge($dept, [
                    'gmap_latitude' => null,
                    'gmap_longitude' => null,
                    'zoom' => 9,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
                $count++;
            }
        }

        $this->info("$count départements migrés.");

        return self::SUCCESS;
    }

    private function convert(string $value): string
    {
        $result = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');

        return $result !== false ? $result : $value;
    }
}
