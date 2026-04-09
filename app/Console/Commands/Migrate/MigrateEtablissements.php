<?php

namespace App\Console\Commands\Migrate;

use App\Services\SlugService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateEtablissements extends Command
{
    protected $signature = 'migrate:legacy-etablissements';

    protected $description = 'Migre les établissements, slugs, catégories et administrateurs depuis la base legacy';

    public function handle(): int
    {
        $this->migrateEtablissements();
        $this->migrateSlugs();
        $this->migrateCategories();
        $this->migrateAdmins();

        return self::SUCCESS;
    }

    private function migrateEtablissements(): void
    {
        $this->info('Migration des établissements...');

        $total = DB::connection('legacy')->table('etablissement')->count();
        $bar = $this->output->createProgressBar($total);

        $count = 0;
        DB::connection('legacy')->table('etablissement')->orderBy('id')->chunk(500, function ($rows) use (&$count, $bar) {
            foreach ($rows as $row) {
                $bar->advance();

                $slug = trim($row->url);
                if (! $slug) {
                    $slug = SlugService::generate($row->titre);
                }

                // Garantir l'unicité du slug
                $baseSlug = $slug;
                $i = 1;
                while (DB::table('etablissements')->where('slug', $slug)->exists()) {
                    $slug = $baseSlug.'-'.$i++;
                }

                $createdAt = null;
                if ($row->date_inscription) {
                    $createdAt = is_numeric($row->date_inscription)
                        ? Carbon::createFromTimestamp((int) $row->date_inscription)
                        : Carbon::parse($row->date_inscription);
                }

                DB::table('etablissements')->insert([
                    'type' => $row->type ?: 0,
                    'titre' => $this->convert($row->titre),
                    'slug' => $slug,
                    'email' => trim($row->email) ?: null,
                    'adresse' => $this->convert(trim($row->adresse)) ?: null,
                    'cp' => trim($row->cp) ?: null,
                    'ville' => $this->convert(trim($row->ville)) ?: null,
                    'dept' => trim($row->dept) ?: null,
                    'ville_id' => ($row->idVille && DB::table('villes')->where('id', $row->idVille)->exists()) ? $row->idVille : null,
                    'latitude' => is_numeric($row->latitude) ? $row->latitude : null,
                    'longitude' => is_numeric($row->longitude) ? $row->longitude : null,
                    'rayon' => $row->rayon ?: 0,
                    'description' => $this->convert($row->description_premium ?: $row->description) ?: null,
                    'horaires' => $this->convert($row->horaires) ?: null,
                    'tarifs' => $this->convert($row->tarifs) ?: null,
                    'telephone' => ($t = trim($row->tel)) ? substr($t, 0, 20) : null,
                    'portable' => ($p = trim($row->port)) ? substr($p, 0, 20) : null,
                    'siret' => ($s = preg_replace('/[^0-9]/', '', $row->SIRET ?? '')) ? substr($s, 0, 14) : null,
                    'photo' => trim($row->photo) ?: null,
                    'accroche' => $this->convert(trim($row->accroche)) ?: null,
                    'moyenne' => $row->moyenne ?: 0,
                    'nb_avis' => $row->nb_avis ?: 0,
                    'valide' => (bool) $row->valide,
                    'classement_ville' => $row->classement_ville ?: 0,
                    'legacy_id' => $row->id,
                    'created_at' => $createdAt ?? now(),
                    'updated_at' => now(),
                ]);

                $count++;
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("$count établissements migrés.");
    }

    private function migrateSlugs(): void
    {
        $this->info('Migration des slugs historiques...');

        $rows = DB::connection('legacy')->table('etablissement_url')->get();

        $count = 0;
        foreach ($rows as $row) {
            $etab = DB::table('etablissements')->where('legacy_id', $row->idEtablissement)->first();
            if (! $etab) {
                continue;
            }

            $slug = trim($row->url);
            // Ne pas insérer le slug s'il correspond au slug actuel
            if ($slug === $etab->slug) {
                continue;
            }

            // Éviter les doublons
            if (DB::table('etablissement_slugs')->where('slug', $slug)->exists()) {
                continue;
            }

            $createdAt = $row->date
                ? Carbon::createFromTimestamp((int) $row->date)
                : now();

            DB::table('etablissement_slugs')->insert([
                'slug' => $slug,
                'etablissement_id' => $etab->id,
                'created_at' => $createdAt,
                'updated_at' => now(),
            ]);
            $count++;
        }

        $this->info("$count slugs historiques migrés.");
    }

    private function migrateCategories(): void
    {
        $this->info('Migration des liens établissement-catégorie...');

        $rows = DB::connection('legacy')->table('etablissement_categorie_prestation')->get();

        $count = 0;
        foreach ($rows as $row) {
            $etab = DB::table('etablissements')->where('legacy_id', $row->idEtablissement)->first();
            // Utiliser idSousCategorie si présent, sinon idCategorie
            $legacyCatId = $row->idSousCategorie ?: $row->idCategorie;
            $cat = DB::table('categories')->where('legacy_id', $legacyCatId)->first();

            if (! $etab || ! $cat) {
                continue;
            }

            // Éviter les doublons
            if (DB::table('categorie_etablissement')
                ->where('etablissement_id', $etab->id)
                ->where('categorie_id', $cat->id)
                ->exists()) {
                continue;
            }

            DB::table('categorie_etablissement')->insert([
                'etablissement_id' => $etab->id,
                'categorie_id' => $cat->id,
            ]);
            $count++;
        }

        $this->info("$count liens catégorie-établissement migrés.");
    }

    private function migrateAdmins(): void
    {
        $this->info('Migration des administrateurs d\'établissements...');

        $rows = DB::connection('legacy')->table('client_administrateur')->get();

        $count = 0;
        foreach ($rows as $row) {
            $etab = DB::table('etablissements')->where('legacy_id', $row->idEtablissement)->first();
            $user = DB::table('users')->where('legacy_id', $row->idClient)->first();

            if (! $etab || ! $user) {
                continue;
            }

            if (DB::table('etablissement_user')
                ->where('etablissement_id', $etab->id)
                ->where('user_id', $user->id)
                ->exists()) {
                continue;
            }

            DB::table('etablissement_user')->insert([
                'etablissement_id' => $etab->id,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count++;
        }

        $this->info("$count liens administrateur-établissement migrés.");
    }

    private function convert(string $value): string
    {
        $result = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');

        return $result !== false ? $result : $value;
    }
}
