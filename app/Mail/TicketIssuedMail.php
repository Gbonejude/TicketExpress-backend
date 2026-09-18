<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class TicketIssuedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public readonly Ticket $ticket,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $eventTitle = $this->ticket->ticketType?->event?->title ?? 'Événement';

        return new Envelope(
            subject: 'Votre billet - '.$eventTitle,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $qrPayload = $this->ticket->qr_code ?: $this->ticket->ticket_number;
        $qrPng = \App\Support\QrImage::png($qrPayload, 300, 2);

        return new Content(
            view: 'emails.ticket-issued',
            with: [
                'ticket' => $this->ticket,
                'order' => $this->ticket->order,
                'event' => $this->ticket->ticketType?->event,
                'ticketType' => $this->ticket->ticketType,
                'qrCode' => $this->ticket->qr_code,
                'qrPng' => $qrPng,
                'accessMethod' => $this->ticket->access_method,
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
        if (! $this->ticket->order) {
            return [];
        }

        try {
            $order = $this->ticket->order->load([
                'tickets.ticketType.event.venue',
                'tickets.ticketType.event.organizer',
                'tickets.ticketType.occurrence',
                'items.ticketType.event',
                'payments',
                'user',
            ]);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.ticket', [
                'order' => $order,
            ]);

            $reference = $this->ticket->ticket_number ?: $this->ticket->id;

            return [
                Attachment::fromData(fn () => $pdf->output(), "billet-{$reference}.pdf")
                    ->withMime('application/pdf'),
            ];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Échec génération PDF pour TicketIssuedMail: '.$e->getMessage());

            return [];
        }
    }
}
