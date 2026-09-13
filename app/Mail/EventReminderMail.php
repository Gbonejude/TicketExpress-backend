<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Event;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class EventReminderMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Event $event,
        public readonly Order $order,
    ) {
        $this->onQueue('emails');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rappel : '.$this->event->title.' a lieu bientôt ! - TicketExpress',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event_reminder',
            with: [
                'event' => $this->event,
                'order' => $this->order,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
