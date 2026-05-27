<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAppointmentNotification extends Notification
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

        $mail = (new MailMessage)
            ->subject('Nouveau rendez-vous en ligne')
            ->replyTo($a->customer_email, $a->customer_name)
            ->greeting('Nouveau rendez-vous')
            ->line('**Client :** '.$a->customer_name.' ('.$a->customer_email.')')
            ->line('**Prestation :** '.$a->service_name.' ('.$a->duration_minutes.' min)')
            ->line('**Date :** '.$a->starts_at->locale('fr')->isoFormat('dddd D MMMM YYYY [à] HH[h]mm'))
            ->line('**Praticien :** '.$a->practitioner->name);

        if ($a->customer_phone) {
            $mail->line('**Téléphone :** '.$a->customer_phone);
        }
        if ($a->notes) {
            $mail->line('**Note du client :**')->line($a->notes);
        }

        return $mail->salutation('— TopInstitut');
    }
}
