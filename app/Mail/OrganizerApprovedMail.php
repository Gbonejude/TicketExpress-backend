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
                'loginUrl' => config('app.frontend_url').'/login',
                'dashboardUrl' => config('app.frontend_url').'/organizer/dashboard',
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
