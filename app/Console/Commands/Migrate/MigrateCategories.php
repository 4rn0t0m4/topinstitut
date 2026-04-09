<?php

namespace App\Console\Commands\Migrate;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateCategories extends Command
{
    protected $signature = 'migrate:legacy-categories';
    protected $description = 'Migre les catégories de prestation depuis la base legacy';

    public function handle(): int
    {
        $this->info('Migration des catégories...');

        $rows = DB::connection('legacy')->table('categorie_prestation')->orderBy('id')->get();

        // Premier pass : insérer toutes les catégories
        $legacyToNewId = [];
        foreach ($rows as $row) {
            $newId = DB::table('categories')->insertGetId([
                'nom' => $this->convert($row->titre),
                'parent_id' => null, // sera mis à jour au second pass
                'legacy_id' => $row->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $legacyToNewId[$row->id] = $newId;
        }

        // Second pass : mapper les parent_id (idCategorie dans le legacy)
        $updated = 0;
        foreach ($rows as $row) {
            if ($row->idCategorie > 0 && isset($legacyToNewId[$row->idCategorie])) {
                DB::table('categories')
                    ->where('id', $legacyToNewId[$row->id])
                    ->update(['parent_id' => $legacyToNewId[$row->idCategorie]]);
                $updated++;
            }
        }

        $this->info(count($rows) . " catégories migrées ($updated avec parent).");
        return self::SUCCESS;
    }

    private function convert(string $value): string
    {
        $result = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
        return $result !== false ? $result : $value;
    }
}
