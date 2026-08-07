<?php

declare(strict_types=1);

namespace App\Actions\V1\Ticket;

use App\Actions\Contracts\Action;
use App\Enums\TicketStatus;
use App\Events\Ticket\TicketCheckedInEvent;
use App\Models\Ticket;
use App\Support\CheckInWindow;
use Illuminate\Support\Facades\DB;

/**
 * Validation manuelle d'un billet, depuis le back-office.
 *
 * Le recours quand le QR est illisible : un gestionnaire identifie le billet dans
 * la liste et ouvre l'entrée à la main. Les garanties sont donc les mêmes qu'au
 * portique — même fenêtre horaire, même refus d'un billet déjà consommé — parce
 * qu'un chemin plus permissif que l'autre serait le seul emprunté.
 *
 * @see CheckInWindow pour la règle horaire
 */
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

        $event = $ticket->ticketType?->event;

        // Un billet orphelin d'événement n'ouvre aucune porte : sans date, la
        // fenêtre n'est pas vérifiable, et la refuser vaut mieux que l'ignorer.
        if ($event === null) {
            throw new \Exception('Ce ticket n\'est rattaché à aucun événement.');
        }

        // La fenêtre de la séance visée par le billet, sinon celle de
        // l'événement. Même règle qu'au portique : ce chemin ne doit jamais être
        // le plus permissif des deux.
        $closedReason = CheckInWindow::refusalReasonForTicket($ticket);

        if ($closedReason !== null) {
            throw new \Exception($closedReason);
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
