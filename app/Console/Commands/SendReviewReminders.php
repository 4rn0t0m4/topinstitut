<?php

namespace App\Console\Commands;

use App\Mail\ReviewReminderMail;
use App\Models\ReviewReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendReviewReminders extends Command
{
    protected $signature = 'reminders:send {--limit=50}';

    protected $description = 'Envoie les emails de relance avis dont la date programmée est passée';

    public function handle(): int
    {
        $due = ReviewReminder::whereNull('sent_at')
            ->where('scheduled_at', '<=', now())
            ->with('establishment')
            ->limit((int) $this->option('limit'))
            ->get();

        if ($due->isEmpty()) {
            $this->info('Aucun email à envoyer.');

            return self::SUCCESS;
        }

        $sent = 0;
        foreach ($due as $reminder) {
            if (! $reminder->establishment || ! $reminder->establishment->is_active) {
                $reminder->update(['sent_at' => now()]); // mark as processed
                continue;
            }

            try {
                Mail::to($reminder->email)->send(new ReviewReminderMail($reminder));
                $reminder->update(['sent_at' => now()]);
                $sent++;
            } catch (\Exception $e) {
                $this->warn("Echec pour {$reminder->email}: ".$e->getMessage());
            }
        }

        $this->info("$sent email(s) envoyé(s) sur ".$due->count().'.');

        return self::SUCCESS;
    }
}
