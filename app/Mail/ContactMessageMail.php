<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Message envoyé depuis le formulaire de contact du site public.
 *
 * L'expéditeur reste l'adresse configurée de l'application : envoyer au nom du
 * visiteur ferait échouer SPF/DKIM chez la plupart des hébergeurs. C'est le
 * `replyTo` qui porte son adresse, pour qu'une réponse lui parvienne
 * directement.
 */
final class ContactMessageMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $senderName,
        public readonly string $senderEmail,
        public readonly string $senderPhone,
        public readonly string $subjectLine,
        public readonly string $body,
    ) {
        $this->onQueue('emails');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->senderEmail, $this->senderName)],
            subject: "Contact — {$this->subjectLine}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact_message',
            with: [
                'senderName' => $this->senderName,
                'senderEmail' => $this->senderEmail,
                'senderPhone' => $this->senderPhone,
                'subjectLine' => $this->subjectLine,
                'body' => $this->body,
            ],
        );
    }
}
