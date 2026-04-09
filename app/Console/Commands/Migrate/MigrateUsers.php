<?php

namespace App\Console\Commands\Migrate;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MigrateUsers extends Command
{
    protected $signature = 'migrate:legacy-users';
    protected $description = 'Migre les utilisateurs depuis la base legacy (table client)';

    public function handle(): int
    {
        $this->info('Migration des utilisateurs...');

        $total = DB::connection('legacy')->table('client')->count();
        $bar = $this->output->createProgressBar($total);

        $count = 0;
        $skipped = 0;

        DB::connection('legacy')->table('client')->orderBy('id')->chunk(500, function ($rows) use (&$count, &$skipped, $bar) {
            foreach ($rows as $row) {
                $bar->advance();

                $email = trim($row->email);
                if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $skipped++;
                    continue;
                }

                // Éviter les doublons email
                if (DB::table('users')->where('email', $email)->exists()) {
                    $skipped++;
                    continue;
                }

                $pseudo = $this->convert(trim($row->pseudo)) ?: 'user_' . $row->id;
                // Éviter les doublons pseudo
                while (DB::table('users')->where('pseudo', $pseudo)->exists()) {
                    $pseudo = trim($row->pseudo) . '_' . $row->id;
                }

                $sexe = match (strtolower(trim($row->sexe))) {
                    'male', 'homme', 'm' => 'male',
                    'female', 'femme', 'f' => 'female',
                    default => null,
                };

                $anniversaire = null;
                if ($row->anniversaire && $row->anniversaire !== '--' && $row->anniversaire !== '0000-00-00') {
                    try {
                        $anniversaire = \Carbon\Carbon::parse($row->anniversaire)->format('Y-m-d');
                    } catch (\Exception) {
                    }
                }

                $createdAt = null;
                if ($row->date_inscription) {
                    $createdAt = is_numeric($row->date_inscription)
                        ? \Carbon\Carbon::createFromTimestamp((int) $row->date_inscription)
                        : \Carbon\Carbon::parse($row->date_inscription);
                }

                DB::table('users')->insert([
                    'email' => $email,
                    'password' => $row->password, // MD5 hash conservé tel quel
                    'pseudo' => $this->convert($pseudo),
                    'nom' => $this->convert(trim($row->nom)) ?: null,
                    'prenom' => $this->convert(trim($row->prenom)) ?: null,
                    'sexe' => $sexe,
                    'dept' => trim($row->dept) ?: null,
                    'ville' => $this->convert(trim($row->ville)) ?: null,
                    'ville_id' => ($row->idVille && DB::table('villes')->where('id', $row->idVille)->exists()) ? $row->idVille : null,
                    'longitude' => is_numeric($row->longitude) ? $row->longitude : null,
                    'latitude' => is_numeric($row->latitude) ? $row->latitude : null,
                    'tel_fixe' => trim($row->tel_fixe) ?: null,
                    'tel_port' => trim($row->tel_port) ?: null,
                    'anniversaire' => $anniversaire,
                    'photo' => trim($row->photo) ?: null,
                    'is_admin' => false,
                    'avis_nb' => $row->avis_nb ?: 0,
                    'legacy_id' => $row->id,
                    'email_verified_at' => $row->valide ? ($createdAt ?? now()) : null,
                    'created_at' => $createdAt ?? now(),
                    'updated_at' => now(),
                ]);

                $count++;
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("$count utilisateurs migrés, $skipped ignorés (email invalide ou doublon).");
        return self::SUCCESS;
    }

    private function convert(string $value): string
    {
        $result = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
        return $result !== false ? $result : $value;
    }
}
