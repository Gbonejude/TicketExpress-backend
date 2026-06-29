<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class AdminCredentialsMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $password,
    ) {
        $this->onQueue('emails');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin_credentials',
            with: [
                'userName' => $this->user->first_name.' '.$this->user->last_name,
                'email' => $this->user->email,
                'password' => $this->password,
            ],
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Vos identifiants d\'administration TicketExpress');
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
