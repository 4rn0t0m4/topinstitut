<?php

namespace App\Console\Commands\Migrate;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateAvis extends Command
{
    protected $signature = 'migrate:legacy-avis';
    protected $description = 'Migre les avis et votes utile/inutile depuis la base legacy';

    public function handle(): int
    {
        $this->migrateAvis();
        $this->migrateAvisUtiles();

        return self::SUCCESS;
    }

    private function migrateAvis(): void
    {
        $this->info('Migration des avis...');

        // JOIN avis + avis_note pour récupérer les notes
        $total = DB::connection('legacy')->table('avis')->count();
        $bar = $this->output->createProgressBar($total);

        $count = 0;
        $skipped = 0;

        DB::connection('legacy')->table('avis')->orderBy('id')->chunk(500, function ($rows) use (&$count, &$skipped, $bar) {
            foreach ($rows as $row) {
                $bar->advance();

                $etab = DB::table('etablissements')->where('legacy_id', $row->idEtablissement)->first();
                $user = DB::table('users')->where('legacy_id', $row->idClient)->first();

                if (! $etab || ! $user) {
                    $skipped++;
                    continue;
                }

                // Chercher les notes correspondantes
                $notes = DB::connection('legacy')->table('avis_note')
                    ->where('idAvis', $row->id)
                    ->first();

                $createdAt = $row->date ?: now();

                $reponseDate = null;
                if ($row->reponse_date && $row->reponse_date !== '0000-00-00') {
                    $reponseDate = $row->reponse_date;
                }

                DB::table('avis')->insert([
                    'etablissement_id' => $etab->id,
                    'user_id' => $user->id,
                    'titre' => $this->convert(trim($row->titre)) ?: 'Avis',
                    'contenu' => $this->convert(trim($row->contenu)) ?: '.',
                    'ip' => trim($row->ip) ?: null,
                    'valide' => (bool) $row->valide,
                    'refus' => (bool) $row->refus,
                    'reponse' => $this->convert(trim($row->reponse)) ?: null,
                    'reponse_date' => $reponseDate,
                    'note_accueil' => $notes ? max(1, min(5, (int) $notes->accueil)) : 3,
                    'note_qualite' => $notes ? max(1, min(5, (int) $notes->qualite)) : 3,
                    'note_choix' => $notes ? max(1, min(5, (int) $notes->choix)) : 3,
                    'note_prix' => $notes ? max(1, min(5, (int) $notes->prix)) : 3,
                    'note_cadre' => $notes ? max(1, min(5, (int) $notes->cadre)) : 3,
                    'note_proprete' => $notes ? max(1, min(5, (int) $notes->proprete)) : 3,
                    'legacy_id' => $row->id,
                    'created_at' => $createdAt,
                    'updated_at' => now(),
                ]);

                $count++;
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("$count avis migrés, $skipped ignorés.");
    }

    private function migrateAvisUtiles(): void
    {
        $this->info('Migration des votes utile/inutile...');

        $rows = DB::connection('legacy')->table('avis_utile')->get();

        $count = 0;
        foreach ($rows as $row) {
            $avis = DB::table('avis')->where('legacy_id', $row->idAvis)->first();
            $user = DB::table('users')->where('legacy_id', $row->idClient)->first();

            if (! $avis || ! $user) {
                continue;
            }

            // Éviter les doublons
            if (DB::table('avis_utiles')
                ->where('avis_id', $avis->id)
                ->where('user_id', $user->id)
                ->exists()) {
                continue;
            }

            DB::table('avis_utiles')->insert([
                'avis_id' => $avis->id,
                'user_id' => $user->id,
                'utile' => (bool) $row->utile,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count++;
        }

        $this->info("$count votes utile/inutile migrés.");
    }

    private function convert(string $value): string
    {
        $result = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
        return $result !== false ? $result : $value;
    }
}
