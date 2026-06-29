<?php

declare(strict_types=1);

namespace App\Listeners\Ticket;

use App\Events\Ticket\TicketIssuedEvent;
use App\Jobs\SendEmailJob;
use App\Mail\TicketIssuedMail;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use ReflectionClass;

/**
 * Listener for TicketIssuedEvent.
 * Generates QR code and sends ticket email with PDF.
 */
final class TicketIssuedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the queue the job should be sent to.
     */
    public string $queue = 'notifications';

    /**
     * Handle the event.
     */
    public function handle(TicketIssuedEvent $event): void
    {
        try {
            // Utiliser Reflection pour accéder à la propriété private
            $reflection = new ReflectionClass($event);
            $ticketProperty = $reflection->getProperty('ticket');
            $ticketProperty->setAccessible(true);
            $ticket = $ticketProperty->getValue($event);

            if (! $ticket) {
                Log::warning('Ticket not found in TicketIssuedEvent');

                return;
            }

            $ticket->load(['order', 'ticketType.event']);

            // Generate QR code if not already set
            if (empty($ticket->qr_code)) {
                $ticket->update([
                    'qr_code' => $this->generateQrCode($ticket->id),
                ]);
            }

            // Send email with ticket PDF
            SendEmailJob::dispatch(
                to: $ticket->attendee_email,
                mailableClass: TicketIssuedMail::class,
                mailableData: [$ticket],
            );

            // Log for tracking
            Log::info('Billet émis', [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'order_id' => $ticket->order_id,
                'event_id' => $ticket->ticketType->event->id ?? null,
            ]);
        } catch (Exception $e) {
            Log::error('Échec du traitement du billet émis', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Generate a unique QR code.
     */
    private function generateQrCode(string $ticketId): string
    {
        return hash('sha256', $ticketId.now()->timestamp.config('app.key'));
    }
}
