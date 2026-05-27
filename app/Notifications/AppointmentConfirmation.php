<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentConfirmation extends Notification
{
    use Queueable;

    public function __construct(public Appointment $appointment) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $a = $this->appointment;
        $establishment = $a->establishment;

        return (new MailMessage)
            ->subject('Confirmation de votre rendez-vous - '.$establishment->name)
            ->greeting('Bonjour '.$a->customer_name)
            ->line('Votre rendez-vous est confirmé chez **'.$establishment->name.'**.')
            ->line('**Prestation :** '.$a->service_name)
            ->line('**Date :** '.$a->starts_at->locale('fr')->isoFormat('dddd D MMMM YYYY [à] HH[h]mm'))
            ->line('**Avec :** '.$a->practitioner->name)
            ->when($establishment->address, fn ($m) => $m->line('**Adresse :** '.trim($establishment->address.' '.$establishment->postal_code.' '.$establishment->city)))
            ->action('Voir l\'établissement', url($establishment->url))
            ->line('À bientôt !')
            ->salutation('— '.$establishment->name.' via TopInstitut');
    }
}
