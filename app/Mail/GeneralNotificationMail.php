<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GeneralNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $emailSubject,
        public string $emailMessage,
        public array $data = []
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.general-notification',
            with: [
                'message' => $this->emailMessage,
                'data' => $this->data,
                'clinicName' => config('app.name'),
                'clinicPhone' => config('app.phone', '+1234567890'),
                'clinicAddress' => config('app.address', 'Calle Principal 123'),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}