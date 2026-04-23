<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Establishment;
use App\Models\EstablishmentSlug;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RegenerateEstablishmentSlugs extends Command
{
    protected $signature = 'slugs:regenerate {--dry-run : Affiche sans modifier}';

    protected $description = 'Régénère des slugs courts (nom seul) uniques par ville+type, sauvegarde les anciens dans establishment_slugs';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $count = Establishment::count();
        $this->info("Traitement de $count établissements...");

        $changed = 0;
        $taken = []; // [(city_id, type) => [slug, ...]]

        Establishment::orderBy('id')->chunk(500, function ($chunk) use (&$changed, &$taken, $dryRun) {
            foreach ($chunk as $e) {
                $key = $e->city_id.'|'.$e->type;
                $taken[$key] ??= Establishment::where('city_id', $e->city_id)
                    ->where('type', $e->type)
                    ->where('id', '!=', $e->id)
                    ->pluck('slug')
                    ->filter()
                    ->values()
                    ->all();

                $base = $this->cleanSlug($e);
                if (! $base) {
                    continue;
                }

                $slug = $base;
                $n = 2;
                while (in_array($slug, $taken[$key], true)) {
                    $slug = $base.'-'.$n++;
                }

                if ($slug === $e->slug) {
                    $taken[$key][] = $slug;
                    continue;
                }

                $this->line("  #{$e->id} {$e->slug} → {$slug}");

                if (! $dryRun) {
                    // Save old slug for 301 redirects (avoid duplicates)
                    if ($e->slug) {
                        EstablishmentSlug::firstOrCreate([
                            'slug' => $e->slug,
                            'establishment_id' => $e->id,
                        ]);
                    }

                    $e->slug = $slug;
                    $e->saveQuietly();
                }

                $taken[$key][] = $slug;
                $changed++;
            }
        });

        $this->info($dryRun ? "DRY RUN : $changed changements simulés." : "Fait : $changed slugs régénérés.");

        return self::SUCCESS;
    }

    /**
     * Build a clean slug: strip the city name, postal code, and type label words.
     */
    private function cleanSlug(Establishment $e): string
    {
        $name = $e->name;

        // Strip postal code
        $name = preg_replace('/\b\d{5}\b/', '', $name);

        // Replace punctuation attached to letters with spaces (e.g., "Camille.M" → "Camille M")
        $name = preg_replace('/([a-zA-ZÀ-ÿ])[.\/&](?=[a-zA-ZÀ-ÿ])/u', '$1 ', $name);

        // Strip trailing comma/dash + city
        $cityName = $e->city_id ? optional(City::find($e->city_id))->name : $e->city;
        if ($cityName) {
            $name = preg_replace('/[\s,-]+'.preg_quote($cityName, '/').'\b/iu', '', $name);
        }

        $slug = Str::slug($name);

        // Strip Google Places descriptors (compound phrases kept since "beauté" alone is often brand)
        $stopWords = [
            'institut-de-beaute', 'institut-beaute', 'institut',
            'estheticienne-a-domicile',
            'beauty-salon',
            'thalassotherapie',
        ];
        foreach ($stopWords as $word) {
            $slug = preg_replace('/(^|-)'.preg_quote($word, '/').'(-|$)/', '$1$2', $slug);
        }

        // Strip leading/trailing/double dashes
        $slug = trim(preg_replace('/-+/', '-', $slug), '-');

        return $slug ?: Str::slug($e->name);
    }
}
