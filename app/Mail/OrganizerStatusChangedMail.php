<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Organizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class OrganizerStatusChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Organizer $organizer,
        public bool $isActive,
    ) {
        $this->onQueue('emails');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.organizer.status_changed',
            with: [
                'organizerName' => $this->organizer->company_name,
                'isActive' => $this->isActive,
            ],
        );
    }

    public function envelope(): Envelope
    {
        $subject = $this->isActive
            ? 'Votre compte organisateur est actif sur TicketExpress'
            : 'Votre compte organisateur a été suspendu';

        return new Envelope(subject: $subject);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
