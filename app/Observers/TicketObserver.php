<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Ticket;
use Illuminate\Support\Facades\Log;

final class TicketObserver
{
    public function created(Ticket $ticket): void
    {
        Log::info('Ticket created', ['ticket_id' => $ticket->id]);
        // Dispatch events, invalidate cache, etc.
    }

    public function updated(Ticket $ticket): void
    {
        Log::info('Ticket updated', ['ticket_id' => $ticket->id]);
    }

    public function deleted(Ticket $ticket): void
    {
        Log::info('Ticket deleted', ['ticket_id' => $ticket->id]);
    }
}
