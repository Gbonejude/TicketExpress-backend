<?php

declare(strict_types=1);

namespace App\Listeners\Ticket;

use App\Events\Ticket\TicketCheckedInEvent;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use ReflectionClass;

/**
 * Listener for TicketCheckedInEvent.
 * Updates real-time statistics and notifies organizer.
 */
final class TicketCheckedInListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the queue the job should be sent to.
     */
    public string $queue = 'default';

    /**
     * Handle the event.
     */
    public function handle(TicketCheckedInEvent $event): void
    {
        try {
            // Utiliser Reflection pour accéder à la propriété public readonly
            $reflection = new ReflectionClass($event);
            $ticketProperty = $reflection->getProperty('ticket');
            $ticketProperty->setAccessible(true);
            $ticket = $ticketProperty->getValue($event);

            if (! $ticket) {
                Log::warning('Ticket not found in TicketCheckedInEvent');

                return;
            }

            $ticket->load(['ticketType.event.organizer']);

            // Get event
            $eventModel = $ticket->ticketType->event;

            // Calculate check-in statistics
            $totalTickets = $eventModel->ticketTypes()
                ->withSum('tickets', 'id')
                ->get()
                ->sum('tickets_sum_id');

            $checkedInCount = $eventModel->ticketTypes()
                ->with(['tickets' => fn ($query) => $query->whereNotNull('checked_in_at')])
                ->get()
                ->sum(fn ($type) => $type->tickets->count());

            $checkInPercentage = $totalTickets > 0 ? ($checkedInCount / $totalTickets) * 100 : 0;

            // Send notification to organizer if threshold reached (e.g., 50%, 75%, 90%, 100%)
            $thresholds = [50, 75, 90, 100];
            foreach ($thresholds as $threshold) {
                if ($checkInPercentage >= $threshold && $checkInPercentage < ($threshold + 1)) {
                    // Send OneSignal notification to organizer
                    // $this->oneSignalRepository->sendNotification(...)
                    Log::info("Seuil d'enregistrement atteint: {$threshold}%", [
                        'event_id' => $eventModel->id,
                        'checked_in' => $checkedInCount,
                        'total' => $totalTickets,
                    ]);
                }
            }

            // Log for analytics
            Log::info('Billet scanné', [
                'ticket_id' => $ticket->id,
                'event_id' => $eventModel->id,
                'checked_in_count' => $checkedInCount,
                'total_tickets' => $totalTickets,
                'percentage' => round($checkInPercentage, 2),
            ]);
        } catch (Exception $e) {
            Log::error('Échec du traitement du scan de billet', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
