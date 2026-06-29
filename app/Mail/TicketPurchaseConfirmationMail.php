<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class TicketPurchaseConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public readonly Order $order,
        public readonly bool $includeWhatsAppLink = true,
    ) {
        $this->onQueue('emails');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmation d\'achat - TicketExpress',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket_purchase_confirmation',
            with: [
                'order' => $this->order->load(['tickets.ticketType.event', 'user']),
                'whatsappLink' => $this->includeWhatsAppLink ? $this->generateWhatsAppLink() : null,
                'includeWhatsAppLink' => $this->includeWhatsAppLink,
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
        // Générer le PDF du ticket
        $pdf = Pdf::loadView('pdfs.ticket', [
            'order' => $this->order->load(['tickets.ticketType.event', 'user']),
        ]);

        return [
            Attachment::fromData(fn () => $pdf->output(), "ticket-{$this->order->id}.pdf")
                ->withMime('application/pdf'),
        ];
    }

    /**
     * Generate WhatsApp link with pre-filled message.
     */
    private function generateWhatsAppLink(): string
    {
        $phone = $this->order->phone;

        // Remove '+' and spaces from phone
        $cleanPhone = str_replace(['+', ' ', '-'], '', $phone);

        // Generate message
        $message = $this->generateWhatsAppMessage();

        // Encode message for URL
        $encodedMessage = urlencode($message);

        return "https://wa.me/{$cleanPhone}?text={$encodedMessage}";
    }

    /**
     * Generate WhatsApp message content.
     */
    private function generateWhatsAppMessage(): string
    {
        $order = $this->order->load(['tickets.ticketType.event']);
        $firstTicket = $order->tickets->first();
        $event = $firstTicket?->ticketType?->event;

        $message = "🎟️ *TicketExpress - Confirmation d'achat*\n\n";
        $message .= "Bonjour {$order->first_name} !\n\n";
        $message .= "Merci pour votre achat !\n\n";

        if ($event) {
            $message .= "📅 *Événement:* {$event->title}\n";
            if ($event->start_date) {
                /** @var Carbon $startDate */
                $startDate = $event->start_date;
                $message .= '🗓️ *Date:* '.$startDate->format('d/m/Y H:i')."\n";
            }
            // Note: location is on the Venue model, not Event
            // Would need to load venue relation if needed
        }

        $message .= "🎫 *Nombre de tickets:* {$order->tickets->count()}\n";
        $message .= '💰 *Prix total:* '.number_format($order->total_amount, 0, ',', ' ')." XOF\n\n";
        $message .= "🔢 *Numéro de commande:* {$order->order_number}\n\n";
        $message .= "Votre ticket PDF a été envoyé par email.\n\n";
        $message .= "Présentez votre QR code à l'entrée.\n\n";
        $message .= 'Bon événement ! 🎉';

        return $message;
    }
}
