<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewContactNotification extends Notification
{
    use Queueable;

    public function __construct(public Message $message) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Message via TopInstitut - '.$notifiable->name)
            ->replyTo($this->message->email, $this->message->name)
            ->greeting('Nouveau message')
            ->line('**De :** '.($this->message->name ?: 'Anonyme').' ('.$this->message->email.')')
            ->line($this->message->content)
            ->salutation('— TopInstitut');
    }
}
