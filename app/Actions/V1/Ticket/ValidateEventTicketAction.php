<?php

declare(strict_types=1);

namespace App\Actions\V1\Ticket;

use App\Actions\Contracts\Action;
use App\Enums\TicketStatus;
use App\Events\Ticket\TicketCheckedInEvent;
use App\Models\CheckIn;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Valide un billet à l'entrée d'un événement.
 *
 * Contrairement au check-in existant, qui prend un identifiant de billet et lève
 * une exception, cette action part du **code lu** (numéro ou QR) et renvoie un
 * résultat qualifié. C'est ce que demande un contrôle d'accès : la personne au
 * portique doit savoir *pourquoi* elle refuse — billet inconnu, billet d'un autre
 * événement, déjà passé (et quand), remboursé — et pas seulement que « ça n'a pas
 * marché ». Le code appelant traduit chaque cas, l'action ne décide pas de
 * l'affichage.
 *
 * Chaque validation laisse une ligne dans `check_ins` : la table et son modèle
 * existaient déjà mais rien ne les alimentait, donc l'historique d'entrée d'un
 * événement était vide. Le champ `checked_in_by` du billet, lui, était renseigné
 * depuis le corps de la requête — donc au choix du client, et vide en pratique.
 */
final class ValidateEventTicketAction implements Action
{
    public const RESULT_OK = 'ok';

    public const RESULT_NOT_FOUND = 'not_found';

    public const RESULT_WRONG_EVENT = 'wrong_event';

    public const RESULT_ALREADY_USED = 'already_used';

    public const RESULT_NOT_VALID = 'not_valid';

    /**
     * @param  array{event: Event, code: string, agent: User, device?: string|null}  $data
     * @return array{result: string, message: string, ticket: Ticket|null, foundEvent: Event|null}
     */
    public function execute(array $data): array
    {
        $event = $data['event'];
        $code = trim($data['code']);

        $ticket = $this->findByCode($code);

        if ($ticket === null) {
            return $this->fail(self::RESULT_NOT_FOUND, 'Aucun billet ne correspond à ce code.');
        }

        $ticketEvent = $ticket->ticketType?->event;

        if ($ticketEvent === null || (string) $ticketEvent->id !== (string) $event->id) {
            return [
                'result' => self::RESULT_WRONG_EVENT,
                'message' => $ticketEvent === null
                    ? 'Ce billet n\'est rattaché à aucun événement.'
                    : sprintf('Ce billet est celui de « %s », pas de cet événement.', $ticketEvent->title),
                'ticket' => $ticket,
                'foundEvent' => $ticketEvent,
            ];
        }

        if ($ticket->isCheckedIn()) {
            $scanner = $ticket->checkedInBy;

            return [
                'result' => self::RESULT_ALREADY_USED,
                'message' => sprintf(
                    'Billet déjà validé le %s%s.',
                    $ticket->checked_in_at?->translatedFormat('d/m/Y à H:i'),
                    $scanner === null ? '' : ' par '.$scanner->first_name.' '.$scanner->last_name,
                ),
                'ticket' => $ticket,
                'foundEvent' => $ticketEvent,
            ];
        }

        /** @var TicketStatus $status */
        $status = $ticket->getAttribute('status');

        if ($status !== TicketStatus::VALID) {
            return [
                'result' => self::RESULT_NOT_VALID,
                'message' => sprintf('Billet %s : entrée refusée.', mb_strtolower($status->label())),
                'ticket' => $ticket,
                'foundEvent' => $ticketEvent,
            ];
        }

        $ticket = DB::transaction(function () use ($ticket, $data): Ticket {
            $ticket->update([
                'checked_in_at' => now(),
                // L'agent vient de la session, jamais de la requête.
                'checked_in_by' => $data['agent']->id,
            ]);

            CheckIn::create([
                'ticket_id' => $ticket->id,
                'scanned_by' => $data['agent']->id,
                'scanned_at' => now(),
                'device_info' => $data['device'] ?? null,
            ]);

            event(new TicketCheckedInEvent($ticket));

            return $ticket->fresh(['ticketType.event', 'order', 'checkedInBy']);
        });

        return [
            'result' => self::RESULT_OK,
            'message' => 'Billet validé. Entrée autorisée.',
            'ticket' => $ticket,
            'foundEvent' => $ticket->ticketType?->event,
        ];
    }

    /**
     * Le code lu **est** la charge du QR, et rien d'autre.
     *
     * Le numéro imprimé (`AFRO-2026-0042`) n'est volontairement pas accepté : il
     * est séquentiel, donc devinable, et l'accepter reviendrait à laisser entrer
     * qui sait compter. Il reste la référence qu'on cite et qu'on recherche dans
     * le back-office, pas une clé d'entrée.
     *
     * Pour un billet dont le QR est illisible, le chemin est le bouton Check-in de
     * la liste des billets vendus : l'agent y identifie le billet, l'action est
     * autorisée et tracée.
     */
    private function findByCode(string $code): ?Ticket
    {
        return Ticket::query()
            ->with(['ticketType.event', 'order', 'checkedInBy'])
            ->where('qr_code', $code)
            ->first();
    }

    /**
     * @return array{result: string, message: string, ticket: null, foundEvent: null}
     */
    private function fail(string $result, string $message): array
    {
        return [
            'result' => $result,
            'message' => $message,
            'ticket' => null,
            'foundEvent' => null,
        ];
    }
}
