<?php

declare(strict_types=1);

namespace App\Actions\V1\Ticket;

use App\Actions\Contracts\Action;
use App\Enums\TicketStatus;
use App\Events\Ticket\TicketCheckedInEvent;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

final class CheckInTicketAction implements Action
{
    /**
     * Check in a ticket (scan QR code)
     *
     * @param  array{ticket: Ticket, checked_in_by?: string}  $data
     *
     * @throws \Exception
     */
    public function execute(array $data): Ticket
    {
        $ticket = $data['ticket'];

        if ($ticket->isCheckedIn()) {
            throw new \Exception('Ce ticket a déjà été scanné.');
        }

        /** @var TicketStatus $currentStatus */
        $currentStatus = $ticket->getAttribute('status');

        if ($currentStatus !== TicketStatus::VALID) {
            throw new \Exception('Ce ticket n\'est pas valide pour le check-in.');
        }

        return DB::transaction(function () use ($ticket, $data) {
            $ticket->update([
                'checked_in_at' => now(),
                'checked_in_by' => $data['checked_in_by'] ?? null,
            ]);

            event(new TicketCheckedInEvent($ticket));

            return $ticket->fresh();
        });
    }
}
