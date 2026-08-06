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

final class OrganizerApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public readonly Organizer $organizer,
    ) {
        $this->onQueue('emails');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre compte organisateur a été approuvé ! - TicketExpress',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $user = $this->organizer->user;

        return new Content(
            view: 'emails.organizer_approved',
            with: [
                'organizer' => $this->organizer,
                'user' => $user,
                // The back-office, not the public site: an approved organizer
                // manages their events there and never signs in on the ticket
                // site. Both links point at the same place on purpose — this
                // e-mail is how they learn where to go.
                'loginUrl' => config('app.dashboard_url').'/login',
                'dashboardUrl' => config('app.dashboard_url'),
            ],
        );
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
