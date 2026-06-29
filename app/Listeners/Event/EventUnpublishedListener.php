<?php

declare(strict_types=1);

namespace App\Listeners\Event;

use App\Events\Event\EventUnpublishedEvent;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use ReflectionClass;

final class EventUnpublishedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the queue the job should be sent to.
     */
    public string $queue = 'notifications';

    public function __construct() {}

    public function handle(EventUnpublishedEvent $event): void
    {
        try {
            // Utiliser Reflection pour accéder à la propriété private
            $reflection = new ReflectionClass($event);
            $eventProperty = $reflection->getProperty('event');
            $eventProperty->setAccessible(true);
            $eventModel = $eventProperty->getValue($event);

            if (! $eventModel) {
                Log::warning('Event not found in EventUnpublishedEvent');

                return;
            }

            // Logique métier ici (notifications, etc.)
            // Ex: Informer les utilisateurs intéressés que l'événement n'est plus disponible

            Log::info('Événement dépublié', [
                'event_id' => $eventModel->id,
            ]);
        } catch (Exception $e) {
            Log::error('Échec du traitement de l\'événement dépublié', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
