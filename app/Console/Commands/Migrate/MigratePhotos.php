<?php

namespace App\Console\Commands\Migrate;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MigratePhotos extends Command
{
    protected $signature = 'migrate:legacy-photos
        {--source=/Users/arnaud/Sites/TopInstitut/upload/etablissement : Chemin vers les photos legacy}';

    protected $description = 'Migre les photos d\'établissements depuis la base legacy et copie les fichiers';

    public function handle(): int
    {
        $this->info('Migration des photos...');

        $sourcePath = rtrim($this->option('source'), '/');
        if (! File::isDirectory($sourcePath)) {
            $this->warn("Dossier source introuvable : $sourcePath — migration DB uniquement, sans copie de fichiers.");
        }

        $destPath = storage_path('app/public/etablissements');
        File::ensureDirectoryExists($destPath);

        $rows = DB::connection('legacy')->table('photo_etablissement')->orderBy('idEtablissement')->orderBy('ordre')->get();

        $count = 0;
        $copied = 0;

        foreach ($rows as $row) {
            $etab = DB::table('etablissements')->where('legacy_id', $row->idEtablissement)->first();
            if (! $etab) {
                continue;
            }

            $filename = trim($row->photo);
            if (! $filename) {
                continue;
            }

            DB::table('photos')->insert([
                'etablissement_id' => $etab->id,
                'filename' => $filename,
                'ordre' => $row->ordre ?: 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count++;

            // Copier le fichier physique
            $srcFile = $sourcePath.'/'.$row->idEtablissement.'/'.$filename;
            if (File::exists($srcFile)) {
                $etabDir = $destPath.'/'.$etab->id;
                File::ensureDirectoryExists($etabDir);
                File::copy($srcFile, $etabDir.'/'.$filename);
                $copied++;
            }
        }

        $this->info("$count photos migrées en DB, $copied fichiers copiés.");

        return self::SUCCESS;
    }
}
