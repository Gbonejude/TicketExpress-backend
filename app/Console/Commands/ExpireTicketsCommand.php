<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\Ticket;
use App\Support\CheckInWindow;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

/**
 * Périme les billets que le portique a laissés derrière lui.
 *
 * Un billet restait `valide` indéfiniment : rien ne le rattachait au temps, si
 * bien qu'un billet d'un événement passé depuis six mois s'échangeait encore
 * contre une entrée. La fenêtre de validation ferme désormais la porte, mais
 * elle ne dit rien du statut : sans cette commande, la liste des billets vendus
 * afficherait « Valide » pour des places que plus personne ne peut utiliser, et
 * les rapports compteraient des billets en attente d'un événement terminé.
 *
 * Ne sont périmés que les billets `valide` **jamais scannés** dont l'événement a
 * fermé. Un billet déjà entré garde son historique, un billet remboursé ou
 * annulé garde la trace de la décision qui l'a défait.
 */
final class ExpireTicketsCommand extends Command
{
    protected $signature = 'tickets:expire
                            {--dry-run : Compter les billets concernés sans rien modifier}';

    protected $description = 'Passer à « expiré » les billets valides des événements dont le contrôle d\'accès a fermé.';

    public function handle(): int
    {
        $cutoff = $this->candidateCutoff();
        $isDryRun = (bool) $this->option('dry-run');

        $matched = 0;
        $expired = 0;

        // Deux temps, parce que la marge de fermeture varie d'un événement à
        // l'autre : le SQL rassemble largement les candidats — il ne peut donc
        // en manquer aucun —, puis chaque événement est confronté à *sa*
        // fenêtre. Calculer la borne en base demanderait de l'arithmétique de
        // dates, qui ne s'écrit pas pareil sous SQLite (les tests) et sous
        // MySQL (la production).
        $this->candidates($cutoff)->chunkById(500, function ($tickets) use (&$matched, &$expired, $isDryRun): void {
            $due = $tickets
                ->filter(function (Ticket $ticket): bool {
                    $event = $ticket->ticketType?->event;

                    if ($event === null) {
                        return false;
                    }

                    // Sur la séance du billet quand il en vise une : la séance du
                    // 7 peut être finie alors que l'événement court jusqu'au 9.
                    return CheckInWindow::closesAt($event, $ticket->ticketType->occurrence)->isPast();
                })
                ->pluck('id');

            $matched += $due->count();

            if ($due->isNotEmpty() && ! $isDryRun) {
                $expired += Ticket::query()
                    ->whereIn('id', $due)
                    ->update(['status' => TicketStatus::EXPIRED->value]);
            }
        });

        if ($matched === 0) {
            $this->components->info('Aucun billet à périmer.');

            return self::SUCCESS;
        }

        $this->components->info($isDryRun
            ? "{$matched} billet(s) seraient périmé(s)."
            : "{$expired} billet(s) périmé(s).");

        return self::SUCCESS;
    }

    /**
     * La date d'événement à partir de laquelle une fenêtre *peut* être fermée.
     *
     * C'est la marge la **plus courte** en circulation qui la fixe, pas la plus
     * longue : un événement terminé il y a six heures et tolérant deux heures de
     * retard a déjà fermé, alors qu'un seuil calé sur la marge la plus généreuse
     * ne l'aurait même pas regardé. On ratisse donc au plus large, quitte à
     * ramasser des candidats que la vérification par événement écartera ensuite
     * — un candidat de trop ne coûte rien, un candidat manqué resterait
     * « valide » indéfiniment.
     */
    private function candidateCutoff(): CarbonImmutable
    {
        // Sans événement, `hoursAfter` rend la valeur d'usine — le plancher pour
        // qui n'a rien réglé.
        $shortestHours = CheckInWindow::hoursAfter();

        // `min()` en SQL ignore les NULL : on ne relève que les marges
        // réellement posées, aux deux niveaux qui peuvent en porter une.
        $overrides = [
            Event::query()->min('checkin_close_hours_after'),
            Organizer::query()->min('checkin_close_hours_after'),
        ];

        foreach ($overrides as $override) {
            if ($override !== null) {
                $shortestHours = min($shortestHours, (float) $override);
            }
        }

        return CarbonImmutable::now()->subMinutes((int) round($shortestHours * 60));
    }

    /**
     * Les billets susceptibles d'être périmés : valides, jamais scannés, et dont
     * la date est passée avant la borne la plus permissive.
     *
     * Deux familles, selon ce qui date le billet — sa séance quand il en vise
     * une, l'événement sinon. Les traiter ensemble ferait manquer les billets
     * d'une séance du 7 sur un événement qui court jusqu'au 9 : l'événement
     * n'est pas fini, la séance si.
     *
     * @return Builder<Ticket>
     */
    private function candidates(CarbonImmutable $cutoff): Builder
    {
        return Ticket::query()
            // Jusqu'à l'organisateur : la fenêtre d'un événement muet se lit sur
            // sa fiche, et un chargement paresseux ferait une requête par billet.
            // `ticketType.occurrence` pour la même raison, côté séance.
            ->with(['ticketType.event.organizer', 'ticketType.occurrence'])
            ->where('status', TicketStatus::VALID->value)
            ->whereNull('checked_in_at')
            ->where(function (Builder $dated) use ($cutoff): void {
                $dated
                    // Billets sans séance : la fin de l'événement fait foi.
                    ->whereHas('ticketType', function (Builder $type) use ($cutoff): void {
                        $type->whereNull('occurrence_id')
                            ->whereHas('event', function (Builder $event) use ($cutoff): void {
                                $event
                                    ->where(function (Builder $withEnd) use ($cutoff): void {
                                        $withEnd->whereNotNull('end_date')->where('end_date', '<', $cutoff);
                                    })
                                    ->orWhere(function (Builder $withoutEnd) use ($cutoff): void {
                                        $withoutEnd->whereNull('end_date')->where('start_date', '<', $cutoff);
                                    });
                            });
                    })
                    // Billets datés par leur séance.
                    ->orWhereHas('ticketType.occurrence', function (Builder $occurrence) use ($cutoff): void {
                        $occurrence
                            ->where(function (Builder $withEnd) use ($cutoff): void {
                                $withEnd->whereNotNull('end_date')->where('end_date', '<', $cutoff);
                            })
                            ->orWhere(function (Builder $withoutEnd) use ($cutoff): void {
                                $withoutEnd->whereNull('end_date')->where('start_date', '<', $cutoff);
                            });
                    });
            });
    }
}
