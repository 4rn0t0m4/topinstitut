<?php

namespace App\Mail;

use App\Models\ReviewReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReviewReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ReviewReminder $reminder) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Comment s\'est passée votre visite chez '.$this->reminder->establishment->name.' ?',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.review-reminder',
            with: [
                'reminder' => $this->reminder,
                'establishment' => $this->reminder->establishment,
                'reviewUrl' => url($this->reminder->establishment->url.'#avis?token='.$this->reminder->token.'&email='.urlencode($this->reminder->email)),
            ],
        );
    }
}
