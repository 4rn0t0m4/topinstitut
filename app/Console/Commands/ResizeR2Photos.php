<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ResizeR2Photos extends Command
{
    protected $signature = 'r2:resize-photos
        {--limit=50 : Nombre de photos à traiter par exécution}
        {--width=800 : Largeur max en pixels}
        {--quality=82 : Qualité JPEG (0-100)}
        {--prefix=etablissements/ : Préfixe R2 à scanner}';

    protected $description = 'Redimensionne en place les photos R2 qui dépassent --width (et fixe Cache-Control).';

    public function handle(): int
    {
        if (! extension_loaded('gd')) {
            $this->error('Extension GD non disponible.');

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

            if ($srcW <= $maxWidth) {
                imagedestroy($image);
                $skipped++;
                continue;
            }

            $dstW = $maxWidth;
            $dstH = (int) round($srcH * ($maxWidth / $srcW));
            $resized = imagecreatetruecolor($dstW, $dstH);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
            imagedestroy($image);

            ob_start();
            imagejpeg($resized, null, $quality);
            $newBytes = ob_get_clean();
            imagedestroy($resized);

            $disk->put($key, $newBytes, [
                'ContentType' => 'image/jpeg',
                'CacheControl' => 'public, max-age=15552000, immutable',
            ]);

            $saved = strlen($originalBytes) - strlen($newBytes);
            $savedBytes += $saved;
            $processed++;
            $this->line(sprintf('  OK : %s  (%dx%d → %dx%d, -%s)',
                $key, $srcW, $srcH, $dstW, $dstH, $this->humanSize($saved)));
        }

        $this->info(sprintf('Terminé. Redimensionnés : %d, déjà OK : %d, économisé : %s.',
            $processed, $skipped, $this->humanSize($savedBytes)));

        return self::SUCCESS;
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes < 1024) return $bytes.' o';
        if ($bytes < 1048576) return round($bytes / 1024, 1).' Kio';
        return round($bytes / 1048576, 2).' Mio';
    }
}
