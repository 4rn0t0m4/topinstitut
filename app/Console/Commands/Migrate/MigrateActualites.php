<?php

namespace App\Console\Commands\Migrate;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateActualites extends Command
{
    protected $signature = 'migrate:legacy-actualites';

    protected $description = 'Migre les actualités depuis la base legacy';

    public function handle(): int
    {
        $this->info('Migration des actualités...');

        $rows = DB::connection('legacy')->table('actualite')->orderBy('id')->get();

        $count = 0;
        foreach ($rows as $row) {
            $etab = DB::table('etablissements')->where('legacy_id', $row->idEtablissement)->first();
            if (! $etab) {
                continue;
            }

            $dateLimite = ($row->date_limite && $row->date_limite !== '0000-00-00')
                ? $row->date_limite
                : null;

            $createdAt = null;
            if ($row->last_maj) {
                $createdAt = is_numeric($row->last_maj)
                    ? Carbon::createFromTimestamp((int) $row->last_maj)
                    : Carbon::parse($row->last_maj);
            }

            DB::table('actualites')->insert([
                'etablissement_id' => $etab->id,
                'titre' => $this->convert(trim($row->titre)) ?: 'Actualité',
                'contenu' => $this->convert(trim($row->description)) ?: null,
                'photo' => trim($row->photo) ?: null,
                'date_limite' => $dateLimite,
                'created_at' => $createdAt ?? now(),
                'updated_at' => now(),
            ]);
            $count++;
        }

        $this->info("$count actualités migrées.");

        return self::SUCCESS;
    }

    private function convert(string $value): string
    {
        $result = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');

        return $result !== false ? $result : $value;
    }
}
