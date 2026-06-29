<?php

declare(strict_types=1);

namespace App\Listeners\Event;

use App\Events\Event\EventPublishedEvent;
use App\Helpers\NotificationHelper;
use App\Notifications\EventPublishedNotification;
use App\Services\CacheInvalidationService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use ReflectionClass;

/**
 * Listener for EventPublishedEvent.
 * Sends notifications to interested users and invalidates cache.
 */
final class EventPublishedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the queue the job should be sent to.
     */
    public string $queue = 'notifications';

    /**
     * Create the event listener.
     */
    public function __construct(
        private readonly CacheInvalidationService $cacheService,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(EventPublishedEvent $event): void
    {
        try {
            // Utiliser Reflection pour accéder à la propriété private
            $reflection = new ReflectionClass($event);
            $eventProperty = $reflection->getProperty('event');
            $eventProperty->setAccessible(true);
            $eventModel = $eventProperty->getValue($event);

            if (! $eventModel) {
                Log::warning('Event not found in EventPublishedEvent');

                return;
            }

            $eventModel->load(['category', 'organizer.user']);

            // Send notification to admins about published event
            if ($eventModel->organizer && $eventModel->organizer->user) {
                NotificationHelper::notifyAdmins(
                    new EventPublishedNotification($eventModel, $eventModel->organizer->user)
                );
            }

            // Send push notifications to users interested in this category
            // OneSignal notification to users who follow this organizer or category

            // Invalidate events cache
            $this->cacheService->invalidateEvents();
            $this->cacheService->invalidateEvent($eventModel->id);

            // Log for analytics
            Log::info('Événement publié', [
                'event_id' => $eventModel->id,
                'event_title' => $eventModel->title,
                'organizer_id' => $eventModel->organizer_id,
                'category_id' => $eventModel->category_id,
            ]);
        } catch (Exception $e) {
            Log::error('Échec du traitement de l\'événement publié', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
