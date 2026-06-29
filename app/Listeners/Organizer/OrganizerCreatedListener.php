<?php

declare(strict_types=1);

namespace App\Listeners\Organizer;

use App\Events\Organizer\OrganizerCreatedEvent;
use App\Models\User;
use App\Notifications\OrganizerRegisteredNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use ReflectionClass;

final class OrganizerCreatedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct() {}

    public function handle(OrganizerCreatedEvent $event): void
    {
        try {
            // Utiliser Reflection pour accéder à la propriété private
            $reflection = new ReflectionClass($event);
            $organizerProperty = $reflection->getProperty('organizer');
            $organizerProperty->setAccessible(true);
            $organizer = $organizerProperty->getValue($event);

            if (! $organizer) {
                Log::warning('Organizer not found in OrganizerCreatedEvent');

                return;
            }

            // Notifier tous les admins et super-admins
            $admins = User::role(['admin', 'super-admin'])->get();

            if ($admins->isNotEmpty() && $organizer->user) {
                Notification::send($admins, new OrganizerRegisteredNotification($organizer, $organizer->user));
            }

            Log::info('Organizer created notification sent', [
                'organizer_id' => $organizer->id,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send organizer created notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
