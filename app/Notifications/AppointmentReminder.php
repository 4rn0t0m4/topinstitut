<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class AppointmentReminder extends Notification
{
    use Queueable;

    public function __construct(public Appointment $appointment, public bool $sameDay = false) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $a = $this->appointment;
        $establishment = $a->establishment;

        $cancelUrl = URL::signedRoute('rdv.cancel', [
            'establishment' => $establishment,
            'appointment' => $a,
        ]);

        $intro = $this->sameDay
            ? 'C\'est aujourd\'hui ! Petit rappel de votre rendez-vous.'
            : 'Petit rappel : vous avez rendez-vous demain.';

        return (new MailMessage)
            ->subject('Rappel de votre rendez-vous - '.$establishment->name)
            ->greeting('Bonjour '.$a->customer_name)
            ->line($intro)
            ->line('**Établissement :** '.$establishment->name)
            ->line('**Prestation :** '.$a->service_name)
            ->line('**Date :** '.$a->starts_at->locale('fr')->isoFormat('dddd D MMMM YYYY [à] HH[h]mm'))
            ->line('**Avec :** '.$a->practitioner->name)
            ->when($establishment->address, fn ($m) => $m->line('**Adresse :** '.trim($establishment->address.' '.$establishment->postal_code.' '.$establishment->city)))
            ->line('Un imprévu ? [Annuler ce rendez-vous]('.$cancelUrl.')')
            ->salutation('— '.$establishment->name.' via TopInstitut');
    }
}
