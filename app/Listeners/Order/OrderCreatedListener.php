<?php

declare(strict_types=1);

namespace App\Listeners\Order;

use App\Events\Order\OrderCreatedEvent;
use App\Jobs\SendEmailJob;
use App\Mail\OrderConfirmationMail;
use App\Notifications\NewOrderForOrganizerNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use ReflectionClass;

/**
 * Listener for OrderCreatedEvent.
 * Sends confirmation email to customer and notification to organizer.
 */
final class OrderCreatedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the queue the job should be sent to.
     */
    public string $queue = 'notifications';

    /**
     * Handle the event.
     */
    public function handle(OrderCreatedEvent $event): void
    {
        try {
            // Extract order using Reflection (private readonly property)
            $reflection = new ReflectionClass($event);
            $property = $reflection->getProperty('order');
            $property->setAccessible(true);
            $order = $property->getValue($event);

            if (! $order) {
                Log::warning('Order not found in OrderCreatedEvent');

                return;
            }

            // Send confirmation email to customer
            SendEmailJob::dispatch(
                to: $order->email,
                mailableClass: OrderConfirmationMail::class,
                mailableData: [$order],
            );

            // Send notification to organizer
            // Get organizer from first order item's event
            if ($order->items->isNotEmpty()) {
                $firstItem = $order->items->first();
                if ($firstItem && $firstItem->ticketType && $firstItem->ticketType->event) {
                    $event = $firstItem->ticketType->event;
                    $organizer = $event->organizer;

                    // Send notification to organizer via database
                    if ($organizer && $organizer->user) {
                        $organizerUser = $organizer->user;
                        $organizerUser->notify(new NewOrderForOrganizerNotification($order, $event));
                    }

                    // TODO: Send notification via OneSignal
                    // $this->oneSignalRepository->sendNotification(...)
                }
            }

            // Log for analytics
            Log::info('Commande créée - notifications envoyées', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'total_amount' => $order->total_amount,
            ]);
        } catch (Exception $e) {
            Log::error('Échec du traitement de la commande créée', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
