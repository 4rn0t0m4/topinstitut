<?php

namespace App\Console\Commands;

use App\Models\Establishment;
use App\Models\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImportGooglePhotos extends Command
{
    protected $signature = 'import:google-photos
        {--limit=10 : Nombre d\'établissements à traiter par exécution}
        {--max-photos=5 : Nombre max de photos par établissement}
        {--width=1200 : Largeur max des photos en pixels}';

    protected $description = 'Importe les photos Google pour les établissements existants';

    private string $apiKey;

    public function handle(): int
    {
        $this->apiKey = config('services.google_places.api_key');
        if (! $this->apiKey) {
            $this->error('GOOGLE_PLACES_API_KEY non configurée.');

            return self::FAILURE;
        }

        $limit = (int) $this->option('limit');
        $maxPhotos = (int) $this->option('max-photos');
        $width = (int) $this->option('width');

        // Établissements avec place_id et sans photo en base
        $establishments = Establishment::whereNotNull('google_place_id')
            ->whereDoesntHave('photos')
            ->limit($limit)
            ->get();

        if ($establishments->isEmpty()) {
            $this->info('Aucun établissement à traiter.');

            return self::SUCCESS;
        }

        $totalImported = 0;

        foreach ($establishments as $establishment) {
            $this->line("  {$establishment->name} ({$establishment->city})...");

            // 1. Récupérer la liste des photos
            $response = Http::withHeaders([
                'X-Goog-Api-Key' => $this->apiKey,
                'X-Goog-FieldMask' => 'photos',
            ])->timeout(15)->get("https://places.googleapis.com/v1/places/{$establishment->google_place_id}", [
                'languageCode' => 'fr',
            ]);

            if (! $response->ok()) {
                $this->warn('    Erreur API : ' . $response->json('error.message', 'Inconnue'));

                continue;
            }

            $photos = array_slice($response->json('photos', []), 0, $maxPhotos);

            if (empty($photos)) {
                $this->line('    Aucune photo disponible.');

                continue;
            }

            $imported = 0;
            foreach ($photos as $index => $photo) {
                $photoName = $photo['name'] ?? null;
                if (! $photoName) {
                    continue;
                }

                $photoUrl = "https://places.googleapis.com/v1/{$photoName}/media?key={$this->apiKey}&maxWidthPx={$width}";
                $filename = 'google_' . ($index + 1) . '.jpg';
                $path = "etablissements/{$establishment->id}/{$filename}";

                try {
                    $imageResponse = Http::timeout(20)->withOptions(['stream' => false])->get($photoUrl);

                    if (! $imageResponse->ok()) {
                        $this->warn("    Photo $index : HTTP {$imageResponse->status()}");

                        continue;
                    }

                    Storage::disk('r2')->put($path, $imageResponse->body(), [
                        'ContentType' => 'image/jpeg',
                    ]);

                    Photo::create([
                        'establishment_id' => $establishment->id,
                        'filename' => $filename,
                        'sort_order' => $index,
                    ]);

                    $imported++;
                    usleep(100000);
                } catch (\Exception $e) {
                    $this->warn("    Erreur photo $index : " . $e->getMessage());
                }
            }

            $this->info("    {$imported} photo(s) importée(s).");
            $totalImported += $imported;
        }

        $this->info("Total : {$totalImported} photo(s) importée(s).");

        return self::SUCCESS;
    }
}
