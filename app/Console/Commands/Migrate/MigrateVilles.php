<?php

namespace App\Console\Commands\Migrate;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateVilles extends Command
{
    protected $signature = 'migrate:legacy-villes';
    protected $description = 'Migre les villes depuis la base legacy';

    public function handle(): int
    {
        $this->info('Migration des villes...');

        $total = DB::connection('legacy')->table('ville')->count();
        $bar = $this->output->createProgressBar($total);

        $count = 0;
        $seenUrls = [];

        DB::connection('legacy')->table('ville')->orderBy('id')->chunk(1000, function ($rows) use (&$count, &$seenUrls, $bar) {
            $batch = [];
            foreach ($rows as $row) {
                $bar->advance();

                $deptNum = str_pad($row->departement, 2, '0', STR_PAD_LEFT);

                // Garantir l'unicité de l'URL
                $url = $row->url;
                if (isset($seenUrls[$url])) {
                    $url = $url . '-' . $row->id;
                }
                $seenUrls[$url] = true;

                $batch[] = [
                    'id' => $row->id,
                    'nom_ville' => $this->convert($row->nom_ville),
                    'code_postal' => $row->code_postal,
                    'url' => $url,
                    'departement' => $deptNum,
                    'habitants' => $row->habitants ?: 0,
                    'latitude' => $row->latitude ?: null,
                    'longitude' => $row->longitude ?: null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $count++;
            }

            DB::table('villes')->insert($batch);
        });

        $bar->finish();
        $this->newLine();
        $this->info("$count villes migrées.");
        return self::SUCCESS;
    }

    private function convert(string $value): string
    {
        $result = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
        return $result !== false ? $result : $value;
    }
}
