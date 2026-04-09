<?php

namespace App\Console\Commands\Migrate;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateHoraires extends Command
{
    protected $signature = 'migrate:legacy-horaires';
    protected $description = 'Migre les horaires depuis la base legacy';

    private const JOUR_MAP = [
        'lundi' => 1,
        'mardi' => 2,
        'mercredi' => 3,
        'jeudi' => 4,
        'vendredi' => 5,
        'samedi' => 6,
        'dimanche' => 7,
    ];

    public function handle(): int
    {
        $this->info('Migration des horaires...');

        $rows = DB::connection('legacy')->table('horaires')->orderBy('idEtablissement')->orderBy('ordre')->get();

        $count = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $etab = DB::table('etablissements')->where('legacy_id', $row->idEtablissement)->first();
            if (! $etab) {
                $skipped++;
                continue;
            }

            $jour = self::JOUR_MAP[strtolower(trim($row->jour))] ?? null;
            if (! $jour) {
                $skipped++;
                continue;
            }

            // Éviter les doublons (etablissement_id + jour unique)
            if (DB::table('horaires')->where('etablissement_id', $etab->id)->where('jour', $jour)->exists()) {
                $skipped++;
                continue;
            }

            DB::table('horaires')->insert([
                'etablissement_id' => $etab->id,
                'jour' => $jour,
                'matin_ouverture' => $this->parseTime($row->ouvert1),
                'matin_fermeture' => $this->parseTime($row->ferme1),
                'aprem_ouverture' => $this->parseTime($row->ouvert2),
                'aprem_fermeture' => $this->parseTime($row->ferme2),
                'ferme' => (bool) $row->isClosed,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count++;
        }

        $this->info("$count horaires migrés, $skipped ignorés.");
        return self::SUCCESS;
    }

    /**
     * Convertit "9h", "9h30", "14h00", "9:30" → "HH:MM:SS" ou null.
     */
    private function parseTime(?string $value): ?string
    {
        $value = trim($value ?? '');
        if ($value === '') {
            return null;
        }

        // Format "9h30", "14h", "9h00"
        if (preg_match('/^(\d{1,2})h(\d{0,2})$/', $value, $m)) {
            $h = (int) $m[1];
            $min = (int) ($m[2] ?: 0);
            if ($h > 23 || $min > 59) {
                return null;
            }
            return sprintf('%02d:%02d:00', $h, $min);
        }

        // Format "9:30", "14:00"
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $value, $m)) {
            $h = (int) $m[1];
            $min = (int) $m[2];
            if ($h > 23 || $min > 59) {
                return null;
            }
            return sprintf('%02d:%02d:00', $h, $min);
        }

        return null;
    }
}
