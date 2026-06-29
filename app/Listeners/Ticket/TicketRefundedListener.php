<?php

declare(strict_types=1);

namespace App\Listeners\Ticket;

use App\Events\Ticket\TicketRefundedEvent;
use App\Jobs\SendEmailJob;
use App\Mail\RefundConfirmationMail;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use ReflectionClass;

/**
 * Listener for TicketRefundedEvent.
 * Sends refund confirmation email and notifies organizer.
 */
final class TicketRefundedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the queue the job should be sent to.
     */
    public string $queue = 'notifications';

    /**
     * Handle the event.
     */
    public function handle(TicketRefundedEvent $event): void
    {
        try {
            // Use Reflection to access the public readonly property
            $reflection = new ReflectionClass($event);
            $ticketProperty = $reflection->getProperty('ticket');
            $ticketProperty->setAccessible(true);
            $ticket = $ticketProperty->getValue($event);

            if (! $ticket) {
                Log::warning('Ticket not found in TicketRefundedEvent');

                return;
            }

            $ticket->load(['order', 'ticketType.event.organizer']);

            // Send refund confirmation email to customer
            SendEmailJob::dispatch(
                to: $ticket->order->email,
                mailableClass: RefundConfirmationMail::class,
                mailableData: [$ticket],
            );

            // Log refund for analytics
            Log::info('Ticket remboursé', [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'order_id' => $ticket->order->id,
                'event_id' => $ticket->ticketType->event->id,
                'reason' => $ticket->refund_reason,
                'refunded_at' => $ticket->refunded_at,
            ]);

            // Notify organizer
            Log::info('Notification organisateur - Remboursement', [
                'organizer_id' => $ticket->ticketType->event->organizer->id,
                'event_id' => $ticket->ticketType->event->id,
                'ticket_id' => $ticket->id,
            ]);

            // TODO: Send push notification to organizer via OneSignal
            // $this->oneSignalRepository->sendNotification(...)
        } catch (Exception $e) {
            Log::error('Échec du traitement du remboursement de billet', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
