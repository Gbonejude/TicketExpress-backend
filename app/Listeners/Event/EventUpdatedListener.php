<?php

declare(strict_types=1);

namespace App\Listeners\Event;

use App\Events\Event\EventUpdatedEvent;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
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
            // Utiliser Reflection pour accéder à la propriété private
            $reflection = new ReflectionClass($event);
            $eventProperty = $reflection->getProperty('event');
            $eventProperty->setAccessible(true);
            $eventModel = $eventProperty->getValue($event);

            if (! $eventModel) {
                Log::warning('Event not found in EventUpdatedEvent');

                return;
            }

            // Logique métier ici (notifications, etc.)
            // Ex: Notifier les utilisateurs ayant déjà acheté des billets

            Log::info('Événement mis à jour', [
                'event_id' => $eventModel->id,
            ]);
        } catch (Exception $e) {
            Log::error('Échec du traitement de la mise à jour d\'événement', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
