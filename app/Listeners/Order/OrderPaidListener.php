<?php

declare(strict_types=1);

namespace App\Listeners\Order;

use App\Enums\TicketStatus;
use App\Events\Order\OrderPaidEvent;
use App\Events\Ticket\TicketIssuedEvent;
use App\Jobs\SendEmailJob;
use App\Mail\TicketPurchaseConfirmationMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\TicketDownloadLink;
use App\Services\TicketDownloadService;
use App\Support\TicketNumber;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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

            $order->load(['items.ticketType.event', 'tickets']);

            // Generate tickets for each order item.
            //
            // Le billet est au porteur : il est émis au nom de l'acheteur, et
            // c'est le QR — unique par billet — qui fait autorité à l'entrée.
            // Acheter quatre places puis les transmettre à ses proches est donc
            // le cas normal, et ne demande aucune saisie.
            foreach ($order->items as $item) {
                // Le numéro porte l'événement et une séquence (AFRO-2026-0042).
                // Le préfixe et le point de départ sont calculés une fois par
                // ligne, puis incrémentés : une commande de dix places ne fait pas
                // dix comptages.
                $prefix = TicketNumber::prefix($item->ticketType?->event);
                $year = now()->year;
                $sequence = TicketNumber::nextSequence($prefix, $year);

                for ($i = 0; $i < $item->quantity; $i++) {
                    $ticket = $this->createTicket($order, $item, $prefix, $year, $sequence);
                    $sequence = (int) $this->sequenceOf($ticket->ticket_number, $prefix, $year) + 1;

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
    /**
     * Crée un billet, en réessayant si le numéro vient d'être pris.
     *
     * Deux paiements simultanés peuvent viser la même séquence : l'index unique
     * en rejette un, et on repart du premier numéro réellement libre. Sans cette
     * reprise, le second paiement échouerait alors que l'argent est encaissé.
     */
    private function createTicket(Order $order, OrderItem $item, string $prefix, int $year, int $sequence): Ticket
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                return $order->tickets()->create([
                    'ticket_type_id' => $item->ticket_type_id,
                    'attendee_name' => $order->first_name.' '.$order->last_name,
                    'attendee_email' => $order->email,
                    'ticket_number' => TicketNumber::format($prefix, $year, $sequence),
                    'qr_code' => $this->generateQrCode(),
                    'status' => TicketStatus::VALID,
                ]);
            } catch (UniqueConstraintViolationException) {
                $sequence = TicketNumber::nextSequence($prefix, $year);
            }
        }

        throw new \RuntimeException("Impossible d'attribuer un numéro de billet pour {$prefix}-{$year}.");
    }

    /** La séquence lue sur un numéro déjà attribué. */
    private function sequenceOf(string $ticketNumber, string $prefix, int $year): string
    {
        return str_replace("{$prefix}-{$year}-", '', $ticketNumber);
    }

    /**
     * La charge du QR code : c'est **le** secret du billet.
     *
     * Aléatoire cryptographique, et non plus `uniqid()`. `uniqid()` dérive de
     * l'horloge : deux billets émis dans la même seconde ne différaient que d'un
     * caractère, donc celui qui détient un billet pouvait deviner ceux de ses
     * voisins. Depuis que le numéro imprimé est séquentiel — et donc devinable —
     * c'est cette valeur seule qui autorise l'entrée : elle doit être imprévisible.
     *
     * 40 caractères alphanumériques ≈ 238 bits d'entropie, largement au-delà de
     * ce qu'un QR code lit sans peine.
     */
    private function generateQrCode(): string
    {
        return Str::random(40);
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
