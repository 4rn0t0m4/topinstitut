<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMail extends Command
{
    protected $signature = 'mail:test {to}';

    protected $description = 'Envoie un email de test pour vérifier la configuration mail';

    public function handle(): int
    {
        $to = $this->argument('to');

        $this->line('Mailer  : '.config('mail.default'));
        $this->line('From    : '.config('mail.from.address'));
        $this->line('Host    : '.config('mail.mailers.smtp.host'));
        $this->line('To      : '.$to);

        if (config('mail.default') === 'log') {
            $this->warn('⚠ MAIL_MAILER=log → les emails sont écrits dans storage/logs, PAS envoyés.');
        }

        try {
            Mail::raw('Test TopInstitut — si vous lisez ceci, l\'envoi d\'emails fonctionne. '.now(), function ($m) use ($to) {
                $m->to($to)->subject('Test email TopInstitut');
            });
            $this->info('✓ Envoi effectué sans erreur.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('✗ Échec : '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
