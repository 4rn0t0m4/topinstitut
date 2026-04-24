<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FixR2CacheHeaders extends Command
{
    protected $signature = 'r2:fix-cache-headers
        {--limit=100 : Nombre de photos à traiter par exécution}
        {--prefix=etablissements/ : Préfixe R2 à scanner}';

    protected $description = 'Ajoute Cache-Control (6 mois immutable) aux objets R2 existants via CopyObject self-copy';

    public function handle(): int
    {
        $disk = Storage::disk('r2');
        $client = $disk->getClient();
        $bucket = config('filesystems.disks.r2.bucket');
        $prefix = $this->option('prefix');
        $limit = (int) $this->option('limit');

        $files = $disk->allFiles($prefix);
        $this->info(count($files).' objets trouvés avec préfixe "'.$prefix.'".');

        $fixed = 0;
        $skipped = 0;

        foreach ($files as $key) {
            if ($fixed >= $limit) {
                break;
            }

            $head = $client->headObject(['Bucket' => $bucket, 'Key' => $key]);
            $current = $head['CacheControl'] ?? '';

            if (str_contains($current, 'max-age')) {
                $skipped++;
                continue;
            }

            $client->copyObject([
                'Bucket' => $bucket,
                'Key' => $key,
                'CopySource' => rawurlencode($bucket.'/'.$key),
                'MetadataDirective' => 'REPLACE',
                'CacheControl' => 'public, max-age=15552000, immutable',
                'ContentType' => $head['ContentType'] ?? 'image/jpeg',
            ]);

            $fixed++;
            $this->line("  OK : $key");
        }

        $this->info("Terminé. Corrigés : $fixed, déjà OK : $skipped.");

        return self::SUCCESS;
    }
}
