<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Workshop;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkshopReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Workshop $workshop,
        public readonly User $participant,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf('Reminder: %s is tomorrow', $this->workshop->title),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.workshops.reminder',
            with: [
                'workshop' => $this->workshop,
                'participant' => $this->participant,
            ],
        );
    }
}
