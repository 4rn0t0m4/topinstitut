<?php

namespace App\Console\Commands;

use App\Models\Establishment;
use App\Models\Photo;
use Illuminate\Console\Command;

class ImportStats extends Command
{
    protected $signature = 'import:stats';

    protected $description = 'Affiche l\'état d\'avancement des imports Google (photos & reviews)';

    public function handle(): int
    {
        $total = Establishment::count();
        $withPlaceId = Establishment::whereNotNull('google_place_id')->count();
        $withPhotos = Establishment::has('photos')->count();
        $photosChecked = Establishment::whereNotNull('google_photos_checked_at')->count();
        $photosPending = Establishment::whereNotNull('google_place_id')
            ->whereNull('google_photos_checked_at')
            ->whereDoesntHave('photos')
            ->count();
        $reviewsFetched = Establishment::whereNotNull('google_reviews')->count();
        $reviewsPending = Establishment::whereNotNull('google_place_id')
            ->whereNull('google_reviews')
            ->count();
        $photoRows = Photo::count();

        $this->info("Établissements total ........... {$total}");
        $this->info("Avec google_place_id ........... {$withPlaceId}");
        $this->line('');
        $this->info("PHOTOS");
        $this->line("  Avec photos en base .......... {$withPhotos}");
        $this->line("  Total rows dans photos ....... {$photoRows}");
        $this->line("  Marqués checked_at ........... {$photosChecked}");
        $this->line("  En attente (à traiter) ....... {$photosPending}");
        $this->line('');
        $this->info("REVIEWS");
        $this->line("  Avec google_reviews .......... {$reviewsFetched}");
        $this->line("  En attente (à traiter) ....... {$reviewsPending}");

        return self::SUCCESS;
    }
}
