<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

final class WhatsAppService
{
    /**
     * Generate WhatsApp link for ticket confirmation.
     */
    public function generateTicketConfirmationLink(Order $order): string
    {
        $phone = $this->cleanPhoneNumber($order->phone);
        $message = $this->generateTicketConfirmationMessage($order);

        return $this->generateWhatsAppLink($phone, $message);
    }

    /**
     * Generate WhatsApp link for event reminder.
     */
    public function generateEventReminderLink(string $phone, string $eventName, string $eventDate, string $ticketCode): string
    {
        $cleanPhone = $this->cleanPhoneNumber($phone);
        $message = $this->generateEventReminderMessage($eventName, $eventDate, $ticketCode);

        return $this->generateWhatsAppLink($cleanPhone, $message);
    }

    /**
     * Generate WhatsApp link for event cancellation.
     */
    public function generateEventCancellationLink(string $phone, string $eventName, string $cancellationReason, float $refundAmount): string
    {
        $cleanPhone = $this->cleanPhoneNumber($phone);
        $message = $this->generateEventCancellationMessage($eventName, $cancellationReason, $refundAmount);

        return $this->generateWhatsAppLink($cleanPhone, $message);
    }

    /**
     * Clean phone number (remove +, spaces, dashes).
     */
    private function cleanPhoneNumber(string $phone): string
    {
        return str_replace(['+', ' ', '-', '(', ')'], '', $phone);
    }

    /**
     * Generate WhatsApp link with message.
     */
    private function generateWhatsAppLink(string $phone, string $message): string
    {
        $encodedMessage = urlencode($message);

        Log::info('WhatsApp link generated', [
            'phone' => $phone,
            'message_length' => strlen($message),
        ]);

        return "https://wa.me/{$phone}?text={$encodedMessage}";
    }

    /**
     * Generate ticket confirmation message.
     */
    private function generateTicketConfirmationMessage(Order $order): string
    {
        $order->load(['tickets.ticketType.event']);

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
            // Note: location is on the Venue model, would need to load venue if needed
        }

        $message .= "🎫 *Nombre de tickets:* {$order->tickets->count()}\n";
        $message .= '💰 *Prix total:* '.number_format($order->total_amount, 0, ',', ' ')." XOF\n\n";
        $message .= "🔢 *Numéro de commande:* {$order->id}\n\n";

        // Add ticket codes
        if ($order->tickets->count() > 0) {
            $message .= "*Vos codes de ticket:*\n";
            foreach ($order->tickets as $index => $ticket) {
                $message .= ($index + 1).'. '.$ticket->ticket_number."\n";
            }
            $message .= "\n";
        }

        $message .= "📧 Votre ticket PDF a été envoyé par email.\n\n";
        $message .= "Présentez votre QR code à l'entrée.\n\n";
        $message .= 'Bon événement ! 🎉';

        return $message;
    }

    /**
     * Generate event reminder message.
     */
    private function generateEventReminderMessage(string $eventName, string $eventDate, string $ticketCode): string
    {
        $message = "⏰ *Rappel TicketExpress*\n\n";
        $message .= "Bonjour !\n\n";
        $message .= "Votre événement est demain ! 🎉\n\n";
        $message .= "📅 *{$eventName}*\n";
        $message .= "🗓️ {$eventDate}\n\n";
        $message .= "🎫 Votre code: {$ticketCode}\n\n";
        $message .= "N'oubliez pas votre ticket !\n\n";
        $message .= 'À demain ! 👋';

        return $message;
    }

    /**
     * Generate event cancellation message.
     */
    private function generateEventCancellationMessage(string $eventName, string $cancellationReason, float $refundAmount): string
    {
        $message = "❌ *Événement annulé - TicketExpress*\n\n";
        $message .= "Bonjour,\n\n";
        $message .= "L'événement *{$eventName}* a été annulé.\n\n";
        $message .= "📋 *Raison:* {$cancellationReason}\n\n";
        $message .= '💰 *Remboursement:* '.number_format($refundAmount, 0, ',', ' ')." XOF\n";
        $message .= "⏱️ *Délai:* 5-7 jours ouvrés\n\n";
        $message .= "Désolé pour ce désagrément.\n\n";
        $message .= '📧 support@ticketexpress.tg';

        return $message;
    }

    /**
     * Log WhatsApp link generation for analytics.
     */
    public function logWhatsAppLinkClick(Order $order): void
    {
        Log::info('WhatsApp link clicked', [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'phone' => $order->phone,
            'timestamp' => now()->toISOString(),
        ]);
    }
}
