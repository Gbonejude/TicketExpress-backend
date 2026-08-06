<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Event;
use App\Models\Ticket;

/**
 * Le numéro imprimé sur un billet : `AFRO-2026-0042`.
 *
 * Préfixe tiré du titre de l'événement, année, puis une séquence. C'est une
 * **référence**, pas un secret : au portique on voit d'un coup d'œil si le billet
 * est du bon événement, en comptabilité on le cite, et au téléphone on le dicte.
 *
 * Il remplace `TKT-`+`uniqid()`, qui donnait `TKT-6892A1B3C4D5E` — dix-sept
 * caractères illisibles, et surtout dérivés de l'horloge : deux billets émis à la
 * suite ne différaient que du dernier caractère.
 *
 * **Un numéro séquentiel est devinable, et c'est assumé** : ce qui autorise
 * l'entrée est le QR, dont la charge est désormais aléatoire (voir
 * `OrderPaidListener`). La validation ne se fait plus sur le numéro.
 */
final class TicketNumber
{
    /** Longueur du préfixe, et du compteur zéro-comblé. */
    private const PREFIX_LENGTH = 4;

    private const SEQUENCE_PAD = 4;

    /**
     * Préfixe de l'événement : les quatre premières lettres de son titre.
     *
     * Les accents sont repliés et la ponctuation retirée — « Événement d'Été »
     * donne EVEN. Un titre trop court ou sans lettre est complété par des X, pour
     * que tous les numéros aient la même forme.
     */
    public static function prefix(?Event $event): string
    {
        $title = $event->title ?? '';

        // translit ASCII : É → E, à → a. Le //TRANSLIT dépend de la locale, d'où
        // le remplacement manuel des cas courants avant de nettoyer le reste.
        $ascii = strtr($title, [
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ä' => 'A', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'Î' => 'I', 'Ï' => 'I', 'î' => 'i', 'ï' => 'i',
            'Ô' => 'O', 'Ö' => 'O', 'ô' => 'o', 'ö' => 'o',
            'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'Ç' => 'C', 'ç' => 'c',
        ]);

        $letters = strtoupper((string) preg_replace('/[^A-Za-z]/', '', $ascii));

        return str_pad(
            substr($letters, 0, self::PREFIX_LENGTH),
            self::PREFIX_LENGTH,
            'X',
        );
    }

    public static function format(string $prefix, int $year, int $sequence): string
    {
        return sprintf(
            '%s-%d-%s',
            $prefix,
            $year,
            str_pad((string) $sequence, self::SEQUENCE_PAD, '0', STR_PAD_LEFT),
        );
    }

    /**
     * Le premier numéro libre pour ce préfixe et cette année.
     *
     * Compte les billets déjà émis puis avance jusqu'à trouver un trou : le
     * comptage donne le bon numéro dans le cas courant, et le sondage rattrape
     * les suppressions comme les préfixes que deux événements se partagent
     * (« Afrobeats » et « Afrique en fête » donnent tous deux AFRO — la séquence
     * est donc commune au préfixe, pas à l'événement).
     */
    public static function nextSequence(string $prefix, int $year): int
    {
        $like = "{$prefix}-{$year}-%";

        $sequence = Ticket::query()->where('ticket_number', 'like', $like)->count() + 1;

        while (Ticket::query()->where('ticket_number', self::format($prefix, $year, $sequence))->exists()) {
            $sequence++;
        }

        return $sequence;
    }
}
