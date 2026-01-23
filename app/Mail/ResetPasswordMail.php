<?php

namespace App\Mail;

use App\Models\Hr\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable;
    use SerializesModels;
    public array $data;

    public function __construct(array $data)
    {
        $this->data = [
            'user' => User::find($data['user_id']),
            'code' => $data['code'],
        ];
    }



    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Solicitação para redefinição de senha',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reset-password-password',
            with: [
                'user' => $this->data['user'],
                'code' => $this->data['code'],
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
