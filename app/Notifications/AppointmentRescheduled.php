<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentRescheduled extends Notification
{
    use Queueable;

    public function __construct(
        public Appointment $appointment,
        public \Illuminate\Support\Carbon $previousStart,
    ) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $a = $this->appointment;
        $cancelUrl = \Illuminate\Support\Facades\URL::signedRoute('rdv.cancel', ['appointment' => $a->id]);

        return (new MailMessage)
            ->subject('Votre rendez-vous a été modifié')
            ->greeting('Bonjour '.$a->customer_name.',')
            ->line('Votre rendez-vous chez **'.$a->establishment->name.'** a été déplacé.')
            ->line('**Ancien créneau :** '.$this->previousStart->locale('fr')->isoFormat('dddd D MMMM YYYY [à] HH[h]mm'))
            ->line('**Nouveau créneau :** '.$a->starts_at->locale('fr')->isoFormat('dddd D MMMM YYYY [à] HH[h]mm'))
            ->line('**Prestation :** '.$a->service_name)
            ->line('**Praticien :** '.$a->practitioner->name)
            ->action('Annuler ce rendez-vous', $cancelUrl)
            ->line('Si ce nouveau créneau ne vous convient pas, contactez directement l\'établissement.')
            ->salutation('À bientôt, TopInstitut');
    }
}
