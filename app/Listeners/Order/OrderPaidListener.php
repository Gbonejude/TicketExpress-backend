<?php

declare(strict_types=1);

namespace App\Listeners\Order;

use App\Enums\TicketStatus;
use App\Events\Order\OrderPaidEvent;
use App\Events\Ticket\TicketIssuedEvent;
use App\Jobs\SendEmailJob;
use App\Mail\TicketPurchaseConfirmationMail;
use App\Models\Order;
use App\Models\TicketDownloadLink;
use App\Services\TicketDownloadService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use ReflectionClass;

/**
 * Listener for OrderPaidEvent.
 * Generates tickets, sends email with QR codes, and updates statistics.
 */
final class OrderPaidListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the queue the job should be sent to.
     */
    public string $queue = 'notifications';

    /**
     * Handle the event.
     */
    public function handle(OrderPaidEvent $event): void
    {
        try {
            // Extract order using Reflection (private readonly property)
            $reflection = new ReflectionClass($event);
            $property = $reflection->getProperty('order');
            $property->setAccessible(true);
            $order = $property->getValue($event);

            if (! $order) {
                Log::warning('Order not found in OrderPaidEvent');

                return;
            }

            // Update order with paid_at timestamp
            $order->update([
                'paid_at' => now(),
            ]);

            $order->load(['items.ticketType', 'tickets']);

            // Generate tickets for each order item
            foreach ($order->items as $item) {
                for ($i = 0; $i < $item->quantity; $i++) {
                    $ticket = $order->tickets()->create([
                        'ticket_type_id' => $item->ticket_type_id,
                        'attendee_name' => $order->first_name.' '.$order->last_name,
                        'attendee_email' => $order->email,
                        'ticket_number' => $this->generateTicketNumber(),
                        'qr_code' => $this->generateQrCode(),
                        'status' => TicketStatus::VALID,
                    ]);

                    // Dispatch TicketIssuedEvent
                    event(new TicketIssuedEvent($ticket));
                }
            }

            // Create download link (with expiration based on event date)
            $downloadService = app(TicketDownloadService::class);
            $downloadLink = $downloadService->createDownloadLink($order);

            // Reload order with all relations
            $order->load(['tickets.ticketType.event']);

            // Respecter le choix du client pour la livraison des tickets
            match ($order->delivery_method->value) {
                'email' => $this->sendEmailOnly($order),
                'whatsapp' => $this->sendWhatsAppOnly($order, $downloadLink),
                'both' => $this->sendBoth($order, $downloadLink),
                default => throw new \LogicException('Invalid delivery method: '.$order->delivery_method->value),
            };

            // Send push notification to customer
            // OneSignal notification

            // Update sales statistics
            Log::info('Commande payée - billets générés', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_tickets' => $order->tickets->count(),
                'paid_at' => $order->paid_at->toISOString(),
                'delivery_method' => $order->delivery_method->value,
                'download_link_token' => $downloadLink->token,
                'download_link_expires_at' => $downloadLink->expires_at->toISOString(),
            ]);
        } catch (Exception $e) {
            Log::error('Échec du traitement du paiement', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Generate a unique ticket number.
     */
    private function generateTicketNumber(): string
    {
        return 'TKT-'.strtoupper(uniqid());
    }

    /**
     * Generate a unique QR code.
     */
    private function generateQrCode(): string
    {
        return 'QR-'.strtoupper(uniqid());
    }

    /**
     * Send ticket confirmation via email only (no WhatsApp link).
     */
    private function sendEmailOnly(Order $order): void
    {
        try {
            SendEmailJob::dispatch(
                to: $order->email,
                mailableClass: TicketPurchaseConfirmationMail::class,
                mailableData: [$order, false],
            );

            Log::info('Email de confirmation envoyé via Job (email only)', [
                'order_id' => $order->id,
                'email' => $order->email,
            ]);
        } catch (Exception $e) {
            Log::error('Échec envoi email de confirmation', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send ticket confirmation via WhatsApp only (no email).
     * Logs download link for frontend display.
     */
    private function sendWhatsAppOnly(Order $order, TicketDownloadLink $downloadLink): void
    {
        try {
            $downloadService = app(TicketDownloadService::class);
            $downloadUrl = $downloadService->getDownloadUrl($downloadLink);
            $qrImageUrls = $downloadService->getQrImageUrls($downloadLink);
            $whatsappLink = $downloadService->getWhatsAppLink($order, $downloadLink);

            Log::info('Lien téléchargement généré (WhatsApp mode)', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'phone' => $order->phone,
                'download_url' => $downloadUrl,
                'qr_image_urls' => $qrImageUrls,
                'whatsapp_link' => $whatsappLink,
                'expires_at' => $downloadLink->expires_at->toISOString(),
            ]);

            // TODO: Frontend will display these links to the user
        } catch (Exception $e) {
            Log::error('Échec génération liens téléchargement', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send ticket confirmation via both email and WhatsApp.
     */
    private function sendBoth(Order $order, TicketDownloadLink $downloadLink): void
    {
        try {
            // Send email with PDF via Job
            SendEmailJob::dispatch(
                to: $order->email,
                mailableClass: TicketPurchaseConfirmationMail::class,
                mailableData: [$order, true],
            );

            // Generate download and WhatsApp links
            $downloadService = app(TicketDownloadService::class);
            $downloadUrl = $downloadService->getDownloadUrl($downloadLink);
            $qrImageUrls = $downloadService->getQrImageUrls($downloadLink);
            $whatsappLink = $downloadService->getWhatsAppLink($order, $downloadLink);

            Log::info('Email + liens téléchargement envoyés (both mode)', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'email' => $order->email,
                'phone' => $order->phone,
                'download_url' => $downloadUrl,
                'qr_image_urls' => $qrImageUrls,
                'whatsapp_link' => $whatsappLink,
                'expires_at' => $downloadLink->expires_at->toISOString(),
            ]);
        } catch (Exception $e) {
            Log::error('Échec envoi email + WhatsApp', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
