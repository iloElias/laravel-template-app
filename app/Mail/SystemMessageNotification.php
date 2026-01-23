<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SystemMessageNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public array $data)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->data['message']['subject'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
            with: [
                'user' => $this->data['user'],
                'message' => $this->data['message'],
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
