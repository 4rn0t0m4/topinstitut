<?php

namespace App\Console\Commands;

use App\Models\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ResizeR2Photos extends Command
{
    protected $signature = 'r2:resize-photos
        {--limit=50 : Nombre de photos à traiter par exécution}
        {--width=800 : Largeur max en pixels}
        {--quality=80 : Qualité WebP (0-100)}
        {--prefix=etablissements/ : Préfixe R2 à scanner}';

    protected $description = 'Redimensionne + convertit en WebP les photos R2 existantes. Met à jour la DB et supprime les anciens fichiers.';

    public function handle(): int
    {
        if (! function_exists('imagewebp')) {
            $this->error('GD sans support WebP.');

            return self::FAILURE;
        }

        $disk = Storage::disk('r2');
        $maxWidth = (int) $this->option('width');
        $quality = (int) $this->option('quality');
        $limit = (int) $this->option('limit');
        $prefix = $this->option('prefix');

        $files = $disk->allFiles($prefix);
        $this->info(count($files).' objets trouvés avec préfixe "'.$prefix.'".');

        $processed = 0;
        $skipped = 0;
        $savedBytes = 0;

        foreach ($files as $key) {
            if ($processed >= $limit) {
                break;
            }

            if (! preg_match('/\.(jpe?g|png)$/i', $key)) {
                $skipped++;
                continue;
            }

            $originalBytes = $disk->get($key);
            if (! $originalBytes) {
                continue;
            }

            $image = @imagecreatefromstring($originalBytes);
            if (! $image) {
                $this->warn("  Skip (decode): $key");
                continue;
            }

            $srcW = imagesx($image);
            $srcH = imagesy($image);

            if ($srcW > $maxWidth) {
                $dstW = $maxWidth;
                $dstH = (int) round($srcH * ($maxWidth / $srcW));
                $resized = imagecreatetruecolor($dstW, $dstH);
                imagecopyresampled($resized, $image, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
                imagedestroy($image);
                $image = $resized;
            } else {
                $dstW = $srcW;
                $dstH = $srcH;
            }

            ob_start();
            imagewebp($image, null, $quality);
            $newBytes = ob_get_clean();
            imagedestroy($image);

            if (! $newBytes) {
                $this->warn("  Skip (encode): $key");
                continue;
            }

            // Nouveau key avec extension .webp
            $newKey = preg_replace('/\.(jpe?g|png)$/i', '.webp', $key);

            $disk->put($newKey, $newBytes, [
                'ContentType' => 'image/webp',
                'CacheControl' => 'public, max-age=15552000, immutable',
            ]);

            // Mettre à jour l'enregistrement Photo (filename = basename du nouveau key)
            $this->updatePhotoRecord($key, $newKey);

            // Supprimer l'ancien fichier (si différent du nouveau)
            if ($newKey !== $key) {
                $disk->delete($key);
            }

            $saved = strlen($originalBytes) - strlen($newBytes);
            $savedBytes += $saved;
            $processed++;
            $this->line(sprintf('  OK : %s → %s  (%dx%d → %dx%d, -%s)',
                basename($key), basename($newKey), $srcW, $srcH, $dstW, $dstH, $this->humanSize($saved)));
        }

        $this->info(sprintf('Terminé. Convertis : %d, skippés : %d, économisé : %s.',
            $processed, $skipped, $this->humanSize($savedBytes)));

        return self::SUCCESS;
    }

    private function updatePhotoRecord(string $oldKey, string $newKey): void
    {
        $parts = explode('/', $oldKey);
        if (count($parts) < 3) {
            return;
        }

        $establishmentId = (int) $parts[1];
        $oldFilename = end($parts);
        $newFilename = basename($newKey);

        Photo::where('establishment_id', $establishmentId)
            ->where('filename', $oldFilename)
            ->update(['filename' => $newFilename]);
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes < 1024) return $bytes.' o';
        if ($bytes < 1048576) return round($bytes / 1024, 1).' Kio';
        return round($bytes / 1048576, 2).' Mio';
    }
}
