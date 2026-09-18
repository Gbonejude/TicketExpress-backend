<?php

declare(strict_types=1);

namespace App\Listeners\Organizer;

use App\Events\Organizer\OrganizerCreatedEvent;
use App\Models\User;
use App\Notifications\OrganizerApplicationReceivedNotification;
use App\Notifications\OrganizerRegisteredNotification;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

final class OrganizerCreatedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public function __construct() {}

    public function handle(OrganizerCreatedEvent $event): void
    {
        try {
            $organizer = $event->organizer;

            if (! $organizer) {
                Log::warning('Organizer not found in OrganizerCreatedEvent');

                return;
            }

            $organizer->loadMissing('user');

            // 1. Notifier tous les admins et super-admins (email + cloche)
            $admins = User::role(['admin', 'super-admin'])->get();

            if ($admins->isNotEmpty() && $organizer->user) {
                Notification::send($admins, new OrganizerRegisteredNotification($organizer, $organizer->user));
            }

            // 2. Accuser réception par email à l'organisateur qui a fait la demande
            if ($organizer->user && ($organizer->status?->value ?? $organizer->status) === 'pending') {
                $organizer->user->notify(new OrganizerApplicationReceivedNotification($organizer));
            }

            Log::info('Organizer created notifications sent', [
                'organizer_id' => $organizer->id,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send organizer created notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
