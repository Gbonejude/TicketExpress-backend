<?php

declare(strict_types=1);

namespace App\Listeners\Ticket;

use App\Events\Ticket\TicketUsedEvent;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use ReflectionClass;

final class TicketUsedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct() {}

    public function handle(TicketUsedEvent $event): void
    {
        try {
            // Utiliser Reflection pour accéder à la propriété private
            $reflection = new ReflectionClass($event);
            $ticketProperty = $reflection->getProperty('ticket');
            $ticketProperty->setAccessible(true);
            $ticket = $ticketProperty->getValue($event);

            if (! $ticket) {
                Log::warning('Ticket not found in TicketUsedEvent');

                return;
            }

            // Logique métier ici (confirmation de check-in, analytics, etc.)
            // Ex: Envoyer confirmation de check-in au participant

            Log::info('Ticket used notification sent', [
                'ticket_id' => $ticket->id,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send ticket used notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
