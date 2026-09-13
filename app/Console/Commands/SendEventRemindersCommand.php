<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\EventStatus;
use App\Jobs\SendEmailJob;
use App\Mail\EventReminderMail;
use App\Mail\EventStartedMail;
use App\Models\Event;
use App\Models\Order;
use App\Notifications\EventReminderNotification;
use App\Notifications\EventStartedNotification;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class SendEventRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie les rappels 24h avant les événements et les notifications au démarrage de l\'événement.';

    public function handle(): int
    {
        $this->info('Début du traitement des rappels d\'événements...');

        $this->sendUpcomingReminders();
        $this->sendStartedAlerts();

        $this->info('Traitement des rappels terminé.');

        return Command::SUCCESS;
    }

    /**
     * Envoie un rappel pour les événements débutant dans les prochaines 24 heures.
     */
    private function sendUpcomingReminders(): void
    {
        $events = Event::query()
            ->where('status', EventStatus::PUBLISHED)
            ->where('start_date', '>', now())
            ->where('start_date', '<=', now()->addHours(24))
            ->get();

        foreach ($events as $event) {
            $cacheKey = "event_reminder_sent_{$event->id}";

            if (Cache::has($cacheKey)) {
                continue;
            }

            $orders = $this->getEventOrders($event);

            $this->info("Envoi des rappels pour l'événement : {$event->title} ({$orders->count()} commande(s))");

            $notifiedUserIds = [];

            foreach ($orders as $order) {
                // Email
                try {
                    SendEmailJob::dispatch(
                        to: $order->email,
                        mailableClass: EventReminderMail::class,
                        mailableData: [$event, $order],
                    );
                } catch (Exception $e) {
                    Log::error("Échec envoi email de rappel pour commande {$order->id}", ['error' => $e->getMessage()]);
                }

                // In-app notification
                if ($order->user && ! in_array($order->user->id, $notifiedUserIds, true)) {
                    try {
                        $order->user->notify(new EventReminderNotification($event));
                        $notifiedUserIds[] = $order->user->id;
                    } catch (Exception $e) {
                        Log::error("Échec envoi notification in-app rappel pour user {$order->user->id}", ['error' => $e->getMessage()]);
                    }
                }
            }

            // Marquer comme envoyé pour 7 jours
            Cache::put($cacheKey, true, now()->addDays(7));
        }
    }

    /**
     * Envoie une alerte pour les événements venant de commencer.
     */
    private function sendStartedAlerts(): void
    {
        $events = Event::query()
            ->where('status', EventStatus::PUBLISHED)
            ->where('start_date', '<=', now())
            ->where('start_date', '>=', now()->subHours(2))
            ->get();

        foreach ($events as $event) {
            $cacheKey = "event_started_sent_{$event->id}";

            if (Cache::has($cacheKey)) {
                continue;
            }

            $orders = $this->getEventOrders($event);

            $this->info("Envoi des alertes de démarrage pour l'événement : {$event->title} ({$orders->count()} commande(s))");

            $notifiedUserIds = [];

            foreach ($orders as $order) {
                // Email
                try {
                    SendEmailJob::dispatch(
                        to: $order->email,
                        mailableClass: EventStartedMail::class,
                        mailableData: [$event, $order],
                    );
                } catch (Exception $e) {
                    Log::error("Échec envoi email démarrage pour commande {$order->id}", ['error' => $e->getMessage()]);
                }

                // In-app notification
                if ($order->user && ! in_array($order->user->id, $notifiedUserIds, true)) {
                    try {
                        $order->user->notify(new EventStartedNotification($event));
                        $notifiedUserIds[] = $order->user->id;
                    } catch (Exception $e) {
                        Log::error("Échec envoi notification in-app démarrage pour user {$order->user->id}", ['error' => $e->getMessage()]);
                    }
                }
            }

            // Marquer comme envoyé pour 7 jours
            Cache::put($cacheKey, true, now()->addDays(7));
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
        })->with(['user', 'tickets'])->get();
    }
}
