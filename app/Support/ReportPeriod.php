<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

/**
 * La fenêtre de temps d'un rapport, résolue une fois pour toutes.
 *
 * Les écrans de rapport proposent quatre façons de choisir une période — tout,
 * un intervalle, une année, un mois — et chacune doit finir en deux bornes. Les
 * calculer ici plutôt que dans chaque requête évite que « le mois d'août »
 * signifie deux choses différentes selon le compteur qui le lit.
 *
 * `from` et `to` sont nuls quand la période est « tout » : l'appelant n'applique
 * alors aucune contrainte de date.
 */
final readonly class ReportPeriod
{
    public function __construct(
        public string $type,
        public ?CarbonImmutable $from,
        public ?CarbonImmutable $to,
        public string $label,
    ) {}

    /**
     * Construit la période depuis les paramètres de requête.
     *
     * Les bornes sont étendues au jour entier (00:00:00 → 23:59:59) : un rapport
     * demandé « du 1er au 5 » doit contenir les commandes du 5 au soir, ce qu'un
     * `<= 2026-08-05 00:00:00` exclurait.
     */
    public static function fromRequest(Request $request): self
    {
        $type = (string) $request->input('filterType', 'all');

        return match ($type) {
            'range' => self::range(
                $request->input('startDate'),
                $request->input('endDate'),
            ),
            'year' => self::year((int) $request->input('year', now()->year)),
            'month' => self::month(
                (int) $request->input('year', now()->year),
                (int) $request->input('month', now()->month),
            ),
            default => new self('all', null, null, 'Depuis le début'),
        };
    }

    private static function range(?string $start, ?string $end): self
    {
        // Un intervalle sans date de début ne veut rien dire : on retombe sur
        // « tout » plutôt que d'inventer une borne.
        if ($start === null || $start === '') {
            return new self('all', null, null, 'Depuis le début');
        }

        $from = CarbonImmutable::parse($start)->startOfDay();
        $to = CarbonImmutable::parse($end ?: $start)->endOfDay();

        // Bornes inversées : on les remet dans l'ordre plutôt que de renvoyer un
        // rapport vide que personne ne saurait expliquer.
        if ($to->lessThan($from)) {
            [$from, $to] = [$to->startOfDay(), $from->endOfDay()];
        }

        return new self(
            'range',
            $from,
            $to,
            sprintf('Du %s au %s', $from->translatedFormat('d M Y'), $to->translatedFormat('d M Y')),
        );
    }

    private static function year(int $year): self
    {
        $from = CarbonImmutable::create($year, 1, 1)->startOfDay();

        return new self('year', $from, $from->endOfYear(), sprintf('Année %d', $year));
    }

    private static function month(int $year, int $month): self
    {
        $month = max(1, min(12, $month));
        $from = CarbonImmutable::create($year, $month, 1)->startOfDay();

        return new self(
            'month',
            $from,
            $from->endOfMonth(),
            ucfirst($from->translatedFormat('F Y')),
        );
    }

    public function isBounded(): bool
    {
        return $this->from !== null && $this->to !== null;
    }

    /**
     * Le pas de la série temporelle.
     *
     * Un rapport sur une semaine se lit par jour, une année par mois. Le seuil
     * est à 62 jours : au-delà, une courbe journalière devient un peigne
     * illisible.
     */
    public function granularity(): string
    {
        if (! $this->isBounded()) {
            return 'month';
        }

        return $this->from->diffInDays($this->to) > 62 ? 'month' : 'day';
    }
}
