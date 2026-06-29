<?php

declare(strict_types=1);

namespace App\Listeners\Order;

use App\Enums\TicketStatus;
use App\Events\Order\OrderRefundedEvent;
use App\Jobs\SendEmailJob;
use App\Mail\OrderRefundedMail;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use ReflectionClass;

/**
 * Listener for OrderRefundedEvent.
 * Invalidates tickets and sends refund confirmation.
 */
final class OrderRefundedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the queue the job should be sent to.
     */
    public string $queue = 'notifications';

    /**
     * Handle the event.
     */
    public function handle(OrderRefundedEvent $event): void
    {
        try {
            // Utiliser Reflection pour accéder à la propriété private
            $reflection = new ReflectionClass($event);
            $orderProperty = $reflection->getProperty('order');
            $orderProperty->setAccessible(true);
            $order = $orderProperty->getValue($event);

            if (! $order) {
                Log::warning('Order not found in OrderRefundedEvent');

                return;
            }

            // Update order with refunded_at timestamp
            $order->update([
                'refunded_at' => now(),
            ]);

            $order->load(['tickets', 'items.ticketType.event.organizer']);

            // Mark all tickets as invalid/refunded
            $order->tickets()->update([
                'status' => TicketStatus::REFUNDED,
            ]);

            // Send refund confirmation email to customer
            SendEmailJob::dispatch(
                to: $order->email,
                mailableClass: OrderRefundedMail::class,
                mailableData: [$order],
            );

            // Notify organizer about refund
            if ($order->items->isNotEmpty()) {
                $firstItem = $order->items->first();
                if ($firstItem && $firstItem->ticketType && $firstItem->ticketType->event) {
                    $organizer = $firstItem->ticketType->event->organizer;
                    // Send notification to organizer
                    // OneSignal or Email
                }
            }

            // Log for analytics
            Log::info('Commande remboursée', [
                'order_id' => $order->id,
                'refunded_at' => $order->refunded_at->toISOString(),
            ]);
        } catch (Exception $e) {
            Log::error('Échec du traitement du remboursement', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
