<?php

namespace App\Console\Commands;

use App\Models\Establishment;
use App\Services\EmailScraperService;
use Illuminate\Console\Command;

class ScrapeEmails extends Command
{
    protected $signature = 'scrape:emails
        {--limit=5 : Nombre d\'établissements à traiter}
        {--force : Re-scraper même si on a déjà tenté (email vide ou rempli)}';

    protected $description = 'Scrape les sites web des établissements pour récupérer leur email';

    public function handle(EmailScraperService $scraper): int
    {
        $query = Establishment::whereNotNull('website')
            ->where('website', '!=', '');

        if (! $this->option('force')) {
            // Only those never tried (email NULL — empty string '' = déjà tenté)
            $query->whereNull('email');
        }

        $establishments = $query->limit((int) $this->option('limit'))->get();

        if ($establishments->isEmpty()) {
            $this->info('Aucun établissement à traiter.');

            return self::SUCCESS;
        }

        $this->info("{$establishments->count()} site(s) à scraper.");
        $found = 0;

        foreach ($establishments as $etab) {
            $this->line("  {$etab->name} → {$etab->website}");

            $email = $scraper->findEmail($etab->website);

            if ($email) {
                $etab->update(['email' => $email]);
                $this->info("    Trouvé : {$email}");
                $found++;
            } else {
                // Marker '' pour ne pas re-scraper sans --force
                $etab->update(['email' => '']);
                $this->line('    Aucun email trouvé.');
            }

            usleep(500000); // 0.5s entre chaque requête
        }

        $this->info("{$found} email(s) trouvé(s) sur {$establishments->count()} site(s).");

        return self::SUCCESS;
    }
}
