<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateClassements extends Command
{
    protected $signature = 'classement:recalculate';

    protected $description = 'Recalcule le classement des établissements par ville';

    public function handle(): int
    {
        $this->info('Recalcul des classements par ville...');

        // Récupérer toutes les villes qui ont des établissements validés
        $villeIds = DB::table('etablissements')
            ->where('valide', true)
            ->whereNotNull('ville_id')
            ->distinct()
            ->pluck('ville_id');

        $bar = $this->output->createProgressBar($villeIds->count());
        $updated = 0;

        foreach ($villeIds as $villeId) {
            // Récupérer les établissements de cette ville, triés par score
            $etabs = DB::table('etablissements')
                ->where('ville_id', $villeId)
                ->where('valide', true)
                ->where(function ($q) {
                    $q->where('moyenne', '>', 0)->orWhere('nb_avis', '>', 0);
                })
                ->orderByDesc('moyenne')
                ->orderByDesc('nb_avis')
                ->pluck('id');

            // Attribuer le classement
            foreach ($etabs as $rank => $etabId) {
                DB::table('etablissements')
                    ->where('id', $etabId)
                    ->update(['classement_ville' => $rank + 1]);
                $updated++;
            }

            // Les établissements sans avis ni note : classement 0 (non classés)
            DB::table('etablissements')
                ->where('ville_id', $villeId)
                ->where('valide', true)
                ->where('moyenne', 0)
                ->where('nb_avis', 0)
                ->update(['classement_ville' => 0]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("$updated établissements classés dans {$villeIds->count()} villes.");

        return self::SUCCESS;
    }
}
