<?php

namespace App\Console\Commands;

use App\Models\Establishment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportGoogleReviews extends Command
{
    protected $signature = 'import:google-reviews
        {--limit=10 : Nombre d\'établissements à traiter par exécution}';

    protected $description = 'Importe les avis Google pour les établissements existants';

    private string $apiKey;

    public function handle(): int
    {
        $this->apiKey = config('services.google_places.api_key');
        if (! $this->apiKey) {
            $this->error('GOOGLE_PLACES_API_KEY non configurée.');

            return self::FAILURE;
        }

        $limit = (int) $this->option('limit');

        $establishments = Establishment::whereNotNull('google_place_id')
            ->whereNull('google_reviews')
            ->limit($limit)
            ->get();

        if ($establishments->isEmpty()) {
            $this->info('Aucun établissement à traiter.');

            return self::SUCCESS;
        }

        $updated = 0;

        foreach ($establishments as $establishment) {
            $this->line("  {$establishment->name} ({$establishment->city})...");

            $response = Http::withHeaders([
                'X-Goog-Api-Key' => $this->apiKey,
                'X-Goog-FieldMask' => 'reviews,rating,userRatingCount',
            ])->timeout(15)->get("https://places.googleapis.com/v1/places/{$establishment->google_place_id}", [
                'languageCode' => 'fr',
            ]);

            if (! $response->ok()) {
                $this->warn("    Erreur API : {$response->json('error.message', 'Inconnue')}");

                continue;
            }

            $data = $response->json();
            $reviews = $this->formatReviews($data['reviews'] ?? []);

            $establishment->update([
                'google_reviews' => $reviews,
                'google_rating' => $data['rating'] ?? $establishment->google_rating,
                'google_review_count' => $data['userRatingCount'] ?? $establishment->google_review_count,
            ]);

            $count = count($reviews);
            $this->info("    {$count} avis importés.");
            $updated++;

            usleep(100000);
        }

        $this->info("{$updated} établissement(s) mis à jour.");

        return self::SUCCESS;
    }

    private function formatReviews(array $reviews): array
    {
        if (empty($reviews)) {
            return [];
        }

        return collect($reviews)->map(fn ($r) => [
            'author' => $r['authorAttribution']['displayName'] ?? 'Anonyme',
            'rating' => $r['rating'] ?? 0,
            'text' => $r['text']['text'] ?? '',
            'date' => $r['publishTime'] ?? null,
            'photo' => $r['authorAttribution']['photoUri'] ?? null,
        ])->filter(fn ($r) => ! empty($r['text']))->values()->all();
    }
}
