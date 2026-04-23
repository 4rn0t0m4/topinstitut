<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBookingNotification extends Notification
{
    use Queueable;

    public function __construct(public Message $message) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Demande de RDV - '.$notifiable->name)
            ->replyTo($this->message->email, $this->message->name)
            ->greeting('Nouvelle demande de RDV')
            ->line('**Client :** '.$this->message->name.' ('.$this->message->email.')')
            ->line('**Date souhaitée :** '.optional($this->message->requested_date)->format('d/m/Y'))
            ->line('**Horaire :** '.$this->message->requested_time);

        if ($this->message->phone) {
            $mail->line('**Téléphone :** '.$this->message->phone);
        }
        if ($this->message->requested_service) {
            $mail->line('**Prestation :** '.$this->message->requested_service);
        }
        if ($this->message->content) {
            $mail->line('**Message :**')->line($this->message->content);
        }

        return $mail->salutation('— TopInstitut');
    }
}
