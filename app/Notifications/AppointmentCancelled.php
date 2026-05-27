<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentCancelled extends Notification
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

        return (new MailMessage)
            ->subject('Rendez-vous annulé')
            ->greeting('Rendez-vous annulé')
            ->line('Le client a annulé son rendez-vous.')
            ->line('**Client :** '.$a->customer_name.' ('.$a->customer_email.')')
            ->line('**Prestation :** '.$a->service_name)
            ->line('**Date :** '.$a->starts_at->locale('fr')->isoFormat('dddd D MMMM YYYY [à] HH[h]mm'))
            ->line('**Praticien :** '.$a->practitioner->name)
            ->line('Le créneau est de nouveau disponible à la réservation.')
            ->salutation('— TopInstitut');
    }
}
