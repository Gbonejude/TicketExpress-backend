<?php

declare(strict_types=1);

namespace App\Listeners\Event;

use App\Events\Event\EventUpdatedEvent;
use App\Jobs\SendEmailJob;
use App\Mail\EventUpdatedMail;
use App\Models\Event;
use App\Models\Order;
use App\Notifications\EventUpdatedNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use ReflectionClass;

final class EventUpdatedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the queue the job should be sent to.
     */
    public string $queue = 'notifications';

    public function __construct() {}

    public function handle(EventUpdatedEvent $event): void
    {
        try {
            $reflection = new ReflectionClass($event);
            $eventProperty = $reflection->getProperty('event');
            $eventProperty->setAccessible(true);
            /** @var Event|null $eventModel */
            $eventModel = $eventProperty->getValue($event);

            if (! $eventModel) {
                Log::warning('Event not found in EventUpdatedEvent');

                return;
            }

            // Get all orders that have tickets for this event
            $orders = $this->getEventOrders($eventModel);

            Log::info('Envoi des notifications de mise à jour d\'événement', [
                'event_id' => $eventModel->id,
                'event_title' => $eventModel->title,
                'total_orders' => $orders->count(),
            ]);

            $notifiedUserIds = [];

            foreach ($orders as $order) {
                // Send email to order contact email
                try {
                    SendEmailJob::dispatch(
                        to: $order->email,
                        mailableClass: EventUpdatedMail::class,
                        mailableData: [
                            $eventModel,
                            $order,
                            'L\'événement a été mis à jour par l\'organisateur. Consultez les nouvelles informations.',
                        ],
                    );
                } catch (Exception $e) {
                    Log::error('Échec envoi email mise à jour événement', [
                        'order_id' => $order->id,
                        'email' => $order->email,
                        'error' => $e->getMessage(),
                    ]);
                }

                // Send database notification to logged-in user (avoid duplicates if user has multiple orders)
                if ($order->user && ! in_array($order->user->id, $notifiedUserIds, true)) {
                    try {
                        $order->user->notify(new EventUpdatedNotification($eventModel));
                        $notifiedUserIds[] = $order->user->id;
                    } catch (Exception $e) {
                        Log::error('Échec envoi notification in-app mise à jour événement', [
                            'order_id' => $order->id,
                            'user_id' => $order->user->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        } catch (Exception $e) {
            Log::error('Échec du traitement de la mise à jour d\'événement', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @return Collection<int, Order>
     */
    private function getEventOrders(Event $event): Collection
    {
        $ticketTypeIds = $event->ticketTypes()->pluck('id');

        return Order::whereHas('tickets', function ($query) use ($ticketTypeIds) {
            $query->whereIn('ticket_type_id', $ticketTypeIds);
        })->with(['user'])->get();
    }
}
