<?php

declare(strict_types=1);

namespace App\Listeners\Organizer;

use App\Enums\EventStatus;
use App\Events\Event\EventUnpublishedEvent;
use App\Events\Organizer\OrganizerSuspendedEvent;
use App\Jobs\SendEmailJob;
use App\Mail\OrganizerSuspendedMail;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ReflectionClass;

/**
 * Listener for OrganizerSuspendedEvent.
 * Unpublishes all organizer's events and sends notifications.
 */
final class OrganizerSuspendedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the queue the job should be sent to.
     */
    public string $queue = 'notifications';

    /**
     * Handle the event.
     */
    public function handle(OrganizerSuspendedEvent $event): void
    {
        try {
            // Utiliser Reflection pour accéder à la propriété private
            $reflection = new ReflectionClass($event);
            $organizerProperty = $reflection->getProperty('organizer');
            $organizerProperty->setAccessible(true);
            $organizer = $organizerProperty->getValue($event);

            if (! $organizer) {
                Log::warning('Organizer not found in OrganizerSuspendedEvent');

                return;
            }

            $organizer->load(['events', 'user']);

            DB::transaction(function () use ($organizer) {
                // Unpublish all organizer's events
                foreach ($organizer->events as $eventModel) {
                    if ($eventModel->status === EventStatus::PUBLISHED) {
                        $eventModel->update([
                            'status' => EventStatus::DRAFT,
                        ]);

                        // Dispatch EventUnpublishedEvent
                        event(new EventUnpublishedEvent($eventModel));
                    }
                }

                // Send email to organizer
                SendEmailJob::dispatch(
                    to: $organizer->user->email,
                    mailableClass: OrganizerSuspendedMail::class,
                    mailableData: [$organizer],
                );

                // Send OneSignal notification
                // $this->oneSignalRepository->sendNotification(...)
            });

            // Log for analytics
            Log::info('Organisateur suspendu', [
                'organizer_id' => $organizer->id,
                'events_unpublished' => $organizer->events->count(),
            ]);
        } catch (Exception $e) {
            Log::error('Échec du traitement de la suspension d\'organisateur', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
