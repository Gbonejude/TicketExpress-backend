<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Event;
use App\Models\EventOccurrence;
use App\Models\Ticket;
use Carbon\CarbonImmutable;

/**
 * La période pendant laquelle les billets d'un événement peuvent être validés.
 *
 * Un billet donne droit à une entrée **à une date**, et rien dans le modèle ne
 * portait cette évidence : la validation ne regardait que l'identité du billet,
 * jamais l'heure. Un billet du 7 août pouvait donc être consommé le 6 — et son
 * porteur légitime refoulé le lendemain, puisqu'un check-in ne se défait pas.
 *
 * La fenêtre s'ouvre avant le début (on fait entrer le public avant que ça
 * commence) et se ferme après la fin (les retardataires, le rangement).
 *
 * Les deux marges appartiennent à l'organisateur, jamais à la plateforme : c'est
 * lui qui tient le portique et qui sait qu'un concert ouvre deux heures avant et
 * une formation dès sept heures du matin. Elles se lisent à trois niveaux, du
 * plus précis au plus général :
 *
 *  1. **l'événement**, quand il a été créé avec des marges particulières ;
 *  2. **l'organisateur**, sa façon de faire habituelle, posée une fois sur sa
 *     fiche et suivie par tout ce qu'il programme ;
 *  3. **`config/ticketexpress.php`**, la valeur d'usine, pour qui n'a rien dit.
 *
 * Les colonnes sont nullables plutôt que recopiées à la création : un événement
 * muet suit son organisateur, et continue de le suivre quand celui-ci change
 * d'habitude.
 *
 * Cette classe ne fait qu'énoncer la règle. Ceux qui l'appliquent sont
 * `ValidateEventTicketAction` (le portique), `CheckInTicketAction` (la validation
 * manuelle du back-office) et `tickets:expire` (qui périme ce que la fermeture a
 * laissé derrière).
 */
final class CheckInWindow
{
    /**
     * Heures d'ouverture anticipée avant le début de l'événement.
     *
     * L'événement d'abord, son organisateur ensuite, la valeur d'usine en
     * dernier recours.
     */
    public static function hoursBefore(?Event $event = null): float
    {
        return self::resolve(
            $event,
            'checkin_open_hours_before',
            config('ticketexpress.checkin.open_hours_before', 4),
        );
    }

    /**
     * Heures de tolérance après la fin de l'événement.
     */
    public static function hoursAfter(?Event $event = null): float
    {
        return self::resolve(
            $event,
            'checkin_close_hours_after',
            config('ticketexpress.checkin.close_hours_after', 4),
        );
    }

    /**
     * La première valeur renseignée en descendant la hiérarchie.
     *
     * Les deux colonnes portent le même nom sur `events` et sur `organizers`,
     * ce qui permet de n'écrire la règle qu'une fois.
     */
    private static function resolve(?Event $event, string $column, mixed $factory): float
    {
        if ($event?->getAttribute($column) !== null) {
            return self::positiveHours($event->getAttribute($column));
        }

        $organizer = $event?->organizer;

        if ($organizer?->getAttribute($column) !== null) {
            return self::positiveHours($organizer->getAttribute($column));
        }

        return self::positiveHours($factory);
    }

    public static function opensAt(Event $event, ?EventOccurrence $occurrence = null): CarbonImmutable
    {
        $start = $occurrence !== null ? $occurrence->start_date : $event->start_date;

        return CarbonImmutable::parse($start)
            ->subMinutes((int) round(self::hoursBefore($event) * 60));
    }

    /**
     * `end_date` est obligatoire en base ; le repli sur `start_date` couvre les
     * jeux de données anciens plutôt qu'un cas métier.
     */
    public static function closesAt(Event $event, ?EventOccurrence $occurrence = null): CarbonImmutable
    {
        $end = $occurrence !== null
            ? ($occurrence->end_date ?? $occurrence->start_date)
            : ($event->end_date ?? $event->start_date);

        return CarbonImmutable::parse($end)
            ->addMinutes((int) round(self::hoursAfter($event) * 60));
    }

    public static function isOpen(Event $event, ?EventOccurrence $occurrence = null): bool
    {
        return self::refusalReason($event, $occurrence) === null;
    }

    /**
     * La fenêtre du billet, séance comprise.
     *
     * Un billet peut viser une séance précise (« Standard — 9 août ») plutôt que
     * l'événement entier. Sans cette distinction, la fenêtre s'étend du premier
     * au dernier jour et le billet du 9 s'échange contre une entrée le 7 — son
     * porteur se faisant refouler le 9, puisqu'un check-in ne se défait pas.
     *
     * Les marges, elles, restent celles de l'événement puis de l'organisateur :
     * une séance porte une date, pas une façon d'ouvrir les portes.
     *
     * Renvoie `null` quand le billet n'est rattaché à aucun événement — au
     * demandeur de traiter ce cas, qui n'est pas une question d'horaire.
     */
    public static function refusalReasonForTicket(Ticket $ticket): ?string
    {
        $ticketType = $ticket->ticketType;
        $event = $ticketType?->event;

        if ($event === null) {
            return null;
        }

        return self::refusalReason($event, $ticketType->occurrence);
    }

    /**
     * Pourquoi l'entrée est refusée à cet instant, ou `null` si le portique est
     * ouvert.
     *
     * Le message dit l'horaire, pas seulement le refus : au portique, « ouvre à
     * 18:00 » évite l'appel à l'organisateur que « refusé » déclenche. Il porte
     * la date, ce qui suffit à distinguer deux séances du même événement.
     */
    public static function refusalReason(Event $event, ?EventOccurrence $occurrence = null): ?string
    {
        $now = CarbonImmutable::now();
        $opensAt = self::opensAt($event, $occurrence);

        if ($now->lt($opensAt)) {
            return sprintf(
                'Trop tôt : le contrôle d\'accès de « %s » ouvre le %s.',
                $event->title,
                $opensAt->translatedFormat('d/m/Y à H:i'),
            );
        }

        $closesAt = self::closesAt($event, $occurrence);

        if ($now->gt($closesAt)) {
            return sprintf(
                'Trop tard : le contrôle d\'accès de « %s » a fermé le %s.',
                $event->title,
                $closesAt->translatedFormat('d/m/Y à H:i'),
            );
        }

        return null;
    }

    /**
     * L'état de la fenêtre, tel que l'affiche le panneau de contrôle d'accès.
     *
     * Volontairement à l'échelle de l'événement : le panneau annonce l'ouverture
     * du portique, pas celle d'une séance en particulier. Sur un événement à
     * plusieurs dates il indique donc l'amplitude totale, et c'est le verdict de
     * chaque scan — lui, calculé sur la séance du billet — qui fait foi.
     *
     * @return array{isOpen: bool, opensAt: string, closesAt: string, reason: string|null}
     */
    public static function state(Event $event): array
    {
        return [
            'isOpen' => self::isOpen($event),
            'opensAt' => self::opensAt($event)->toIso8601String(),
            'closesAt' => self::closesAt($event)->toIso8601String(),
            'reason' => self::refusalReason($event),
        ];
    }

    /**
     * Une marge négative ouvrirait le portique après le début de l'événement, ou
     * le fermerait avant sa fin. Un réglage aberrant ne doit pas verrouiller une
     * entrée : on retombe sur zéro.
     */
    private static function positiveHours(mixed $value): float
    {
        return max(0.0, (float) $value);
    }
}
