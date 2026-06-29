<?php

declare(strict_types=1);

namespace App\Listeners\Order;

use App\Events\Order\OrderCancelledEvent;
use App\Jobs\SendEmailJob;
use App\Mail\OrderCancelledMail;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use ReflectionClass;

/**
 * Listener for OrderCancelledEvent.
 * Releases stock and sends cancellation confirmation email.
 */
final class OrderCancelledListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the queue the job should be sent to.
     */
    public string $queue = 'default';

    /**
     * Handle the event.
     */
    public function handle(OrderCancelledEvent $event): void
    {
        try {
            // Utiliser Reflection pour accéder à la propriété private
            $reflection = new ReflectionClass($event);
            $orderProperty = $reflection->getProperty('order');
            $orderProperty->setAccessible(true);
            $order = $orderProperty->getValue($event);

            if (! $order) {
                Log::warning('Order not found in OrderCancelledEvent');

                return;
            }

            $order->load(['items.ticketType']);

            // Note: Stock release is already handled atomically in CancelOrderAction
            // No need to decrement here again to avoid double decrement

            Log::info('Commande annulée - stock libéré dans CancelOrderAction', [
                'order_id' => $order->id,
                'items_count' => $order->items->count(),
            ]);

            // Send cancellation confirmation email
            SendEmailJob::dispatch(
                to: $order->email,
                mailableClass: OrderCancelledMail::class,
                mailableData: [$order],
            );

            // Log for analytics
            Log::info('Commande annulée', [
                'order_id' => $order->id,
            ]);
        } catch (Exception $e) {
            Log::error('Échec du traitement de la commande annulée', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
