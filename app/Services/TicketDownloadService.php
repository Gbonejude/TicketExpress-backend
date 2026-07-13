<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\TicketDownloadLink;
use Carbon\Carbon;

final class TicketDownloadService
{
    /**
     * Create download link for an order.
     * Expiration is set to event end date + 7 days.
     */
    public function createDownloadLink(Order $order): TicketDownloadLink
    {
        // Load order with relations
        $order->load(['items.ticketType.event']);

        // Calculate expiration date based on latest event
        $expiresAt = $this->calculateExpirationDate($order);

        // Create or update download link
        return TicketDownloadLink::updateOrCreate(
            ['order_id' => $order->id],
            [
                'expires_at' => $expiresAt,
                'max_downloads' => 50,
            ]
        );
    }

    /**
     * Calculate expiration date for download link.
     * Based on the latest event end date + 7 days.
     */
    private function calculateExpirationDate(Order $order): Carbon
    {
        $latestEventEndDate = null;

        foreach ($order->items as $item) {
            $event = $item->ticketType?->event;

            if (! $event || ! $event->end_date) {
                continue;
            }

            $eventEndDate = Carbon::parse($event->end_date);

            if ($latestEventEndDate === null || $eventEndDate->isAfter($latestEventEndDate)) {
                $latestEventEndDate = $eventEndDate;
            }
        }

        // If no event end date found, default to 30 days
        if ($latestEventEndDate === null) {
            return now()->addDays(30);
        }

        // Add 7 days grace period after event
        return $latestEventEndDate->copy()->addDays(7);
    }

    /**
     * Get public download URL for a link.
     */
    public function getDownloadUrl(TicketDownloadLink $link): string
    {
        return url("/api/v1/tickets/download/{$link->token}");
    }

    /**
     * Get public QR image URLs for each ticket in the order.
     *
     * @return array<int, string>
     */
    public function getQrImageUrls(TicketDownloadLink $link): array
    {
        $order = $link->order->load('tickets');
        $urls = [];

        foreach ($order->tickets as $index => $ticket) {
            $urls[] = url("/api/v1/tickets/qr/{$link->token}/{$ticket->id}");
        }

        return $urls;
    }

    /**
     * Get WhatsApp share link with pre-filled message.
     */
    public function getWhatsAppLink(Order $order, TicketDownloadLink $link): string
    {
        $phone = str_replace(['+', ' ', '-', '(', ')'], '', $order->phone);
        $downloadUrl = $this->getDownloadUrl($link);

        $message = $this->generateWhatsAppMessage($order, $downloadUrl);

        return "https://wa.me/{$phone}?text=".urlencode($message);
    }

    /**
     * Generate WhatsApp message with download link.
     */
    private function generateWhatsAppMessage(Order $order, string $downloadUrl): string
    {
        $order->load(['items.ticketType.event']);

        $firstItem = $order->items->first();
        $event = $firstItem?->ticketType?->event;

        $message = "🎟️ *TicketExpress - Vos tickets*\n\n";
        $message .= "Bonjour {$order->first_name} !\n\n";

        if ($event) {
            $message .= "📅 *Événement:* {$event->title}\n";
            if ($event->start_date) {
                $startDate = Carbon::parse($event->start_date);
                $message .= '🗓️ *Date:* '.$startDate->format('d/m/Y H:i')."\n";
            }
        }

        $message .= "🎫 *Tickets:* {$order->items->sum('quantity')}\n";
        $message .= '💰 *Total:* '.number_format((float) $order->total_amount, 0, ',', ' ')." XOF\n\n";
        $message .= "🔢 *Commande:* #{$order->order_number}\n\n";
        $message .= "📥 *Téléchargez vos tickets:*\n";
        $message .= "{$downloadUrl}\n\n";
        $message .= "Présentez vos QR codes à l'entrée.\n\n";
        $message .= 'Bon événement ! 🎉';

        return $message;
    }
}
