<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FirstLoginMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public array $data)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('email.first_login.subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.first-login',
            with: [
                'user' => $this->data['user'],
                'info' => $this->data['info'],
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
