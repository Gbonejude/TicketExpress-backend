<?php

declare(strict_types=1);

namespace App\Listeners\Organizer;

use App\Events\Organizer\OrganizerUpdatedEvent;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use ReflectionClass;

final class OrganizerUpdatedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct() {}

    public function handle(OrganizerUpdatedEvent $event): void
    {
        try {
            // Utiliser Reflection pour accéder à la propriété private
            $reflection = new ReflectionClass($event);
            $organizerProperty = $reflection->getProperty('organizer');
            $organizerProperty->setAccessible(true);
            $organizer = $organizerProperty->getValue($event);

            if (! $organizer) {
                Log::warning('Organizer not found in OrganizerUpdatedEvent');

                return;
            }

            // Logique métier ici (notifications, invalidation cache, etc.)
            // Ex: Invalider le cache du profil organisateur

            Log::info('Organizer updated notification sent', [
                'organizer_id' => $organizer->id,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send organizer updated notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
