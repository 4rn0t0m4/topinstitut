<?php

namespace App\Console\Commands\Migrate;

use App\Models\Etablissement;
use App\Services\RatingService;
use Illuminate\Console\Command;

class RecalculateRatings extends Command
{
    protected $signature = 'migrate:recalculate-ratings';

    protected $description = 'Recalcule moyenne et nb_avis pour tous les établissements';

    public function handle(RatingService $ratingService): int
    {
        $this->info('Recalcul des notes...');

        $total = Etablissement::count();
        $bar = $this->output->createProgressBar($total);

        $updated = 0;
        Etablissement::chunk(200, function ($etablissements) use ($ratingService, &$updated, $bar) {
            foreach ($etablissements as $etab) {
                $ratingService->recalculate($etab);
                $updated++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("$updated établissements recalculés.");

        return self::SUCCESS;
    }
}
