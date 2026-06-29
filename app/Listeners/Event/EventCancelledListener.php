<?php

declare(strict_types=1);

namespace App\Listeners\Event;

use App\Actions\V1\Ticket\RefundTicketAction;
use App\Enums\TicketStatus;
use App\Events\Event\EventCancelledEvent;
use App\Jobs\SendEmailJob;
use App\Mail\EventCancelledMail;
use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use ReflectionClass;

/**
 * Listener for EventCancelledEvent.
 * Sends cancellation emails to all ticket holders and processes automatic refunds.
 */
final class EventCancelledListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the queue the job should be sent to.
     */
    public string $queue = 'notifications';

    public function __construct(
        private readonly RefundTicketAction $refundTicketAction,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(EventCancelledEvent $event): void
    {
        try {
            // Extract event and cancellationReason using Reflection (private readonly properties)
            $reflection = new ReflectionClass($event);

            $eventProperty = $reflection->getProperty('event');
            $eventProperty->setAccessible(true);
            $cancelledEvent = $eventProperty->getValue($event);

            $reasonProperty = $reflection->getProperty('cancellationReason');
            $reasonProperty->setAccessible(true);
            $cancellationReason = $reasonProperty->getValue($event);

            if (! $cancelledEvent) {
                Log::warning('Event not found in EventCancelledEvent');

                return;
            }

            // Get all orders for this event's tickets
            $orders = $this->getEventOrders($cancelledEvent);

            Log::info('Envoi des emails d\'annulation et traitement des remboursements', [
                'event_id' => $cancelledEvent->id,
                'event_name' => $cancelledEvent->name,
                'total_orders' => $orders->count(),
            ]);

            // Send cancellation email to each order customer
            foreach ($orders as $order) {
                try {
                    SendEmailJob::dispatch(
                        to: $order->email,
                        mailableClass: EventCancelledMail::class,
                        mailableData: [
                            $cancelledEvent,
                            $order,
                            $cancellationReason ?: 'Événement annulé par l\'organisateur',
                        ],
                    );

                    Log::info('Email d\'annulation envoyé via Job', [
                        'order_id' => $order->id,
                        'email' => $order->email,
                    ]);
                } catch (Exception $e) {
                    Log::error('Échec envoi email d\'annulation', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Process automatic refunds for all valid tickets
            $this->processAutomaticRefunds($cancelledEvent, $cancellationReason);

            Log::info('Traitement annulation événement terminé', [
                'event_id' => $cancelledEvent->id,
                'emails_sent' => $orders->count(),
            ]);
        } catch (Exception $e) {
            Log::error('Échec du traitement de l\'annulation d\'événement', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Process automatic refunds for all valid tickets of the cancelled event.
     */
    private function processAutomaticRefunds(Event $event, ?string $cancellationReason): void
    {
        try {
            // Get all ticket types for this event
            $ticketTypeIds = $event->ticketTypes()->pluck('id');

            // Get all valid tickets for these ticket types
            $tickets = Ticket::whereIn('ticket_type_id', $ticketTypeIds)
                ->where('status', TicketStatus::VALID)
                ->get();

            $refundedCount = 0;
            $failedCount = 0;

            foreach ($tickets as $ticket) {
                try {
                    $this->refundTicketAction->execute([
                        'ticket' => $ticket,
                        'reason' => $cancellationReason ?? 'Événement annulé par l\'organisateur',
                        'force_refund' => true, // Bypass voluntary refund rules
                    ]);
                    $refundedCount++;
                } catch (Exception $e) {
                    $failedCount++;
                    Log::error('Échec remboursement automatique ticket', [
                        'ticket_id' => $ticket->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Remboursements automatiques traités', [
                'event_id' => $event->id,
                'total_tickets' => $tickets->count(),
                'refunded' => $refundedCount,
                'failed' => $failedCount,
            ]);
        } catch (Exception $e) {
            Log::error('Échec du traitement des remboursements automatiques', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get all unique orders for tickets of this event.
     *
     * @return Collection<int, Order>
     */
    private function getEventOrders(Event $event): Collection
    {
        // Get all ticket types for this event
        $ticketTypeIds = $event->ticketTypes()->pluck('id');

        // Get all tickets for these ticket types
        $tickets = Ticket::whereIn('ticket_type_id', $ticketTypeIds)->get();

        // Get all unique orders for these tickets
        $orderIds = $tickets->pluck('order_id')->unique();

        // Load orders with necessary relations
        return Order::whereIn('id', $orderIds)
            ->with(['tickets.ticketType', 'user'])
            ->get();
    }
}
