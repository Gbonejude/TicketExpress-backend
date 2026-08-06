<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Events
 *
 * Les événements qui se déroulent en ce moment — l'écran d'exploitation du
 * back-office : ce qui tourne, où en est le remplissage, et où en sont les
 * entrées.
 *
 * « En cours » se décide sur l'intervalle : commencé et pas encore terminé. Seuls
 * les événements publiés y figurent — un brouillon ou un événement annulé dont
 * les dates couvrent l'instant ne se déroule pas.
 *
 * Le `orWhereNull('end_date')` plus bas est là par symétrie avec le filtre
 * `when=upcoming` de la liste publique : la colonne est NOT NULL, donc la branche
 * ne se déclenche pas aujourd'hui.
 *
 * @authenticated
 */
final class OngoingEventController extends Controller
{
    /**
     * Ongoing events
     *
     * @queryParam soon_hours int Also return events starting within this many hours (default 24, max 168). Example: 48
     *
     * @response 200 {
     *   "success": true,
     *   "message": "",
     *   "data": {
     *     "ongoing": [
     *       {
     *         "id": "01HXE2K3M4N5P6Q7R8S9T0V1W1",
     *         "title": "Afrobeats Sunset Live",
     *         "sold": 120,
     *         "scanned": 87,
     *         "attendanceRate": 72
     *       }
     *     ],
     *     "soon": [],
     *     "totals": {"ongoing": 1, "sold": 120, "scanned": 87}
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $soonHours = min(max((int) $request->input('soon_hours', 24), 1), 168);

        $ongoing = $this->baseQuery($request->user())
            ->where('start_date', '<=', now())
            ->where(function (Builder $q): void {
                $q->where('end_date', '>=', now())->orWhereNull('end_date');
            })
            ->orderBy('start_date')
            ->get();

        // « Bientôt » évite un écran vide entre deux événements, et donne le
        // temps de préparer les portiques.
        $soon = $this->baseQuery($request->user())
            ->where('start_date', '>', now())
            ->where('start_date', '<=', now()->addHours($soonHours))
            ->orderBy('start_date')
            ->get();

        return $this->success([
            'ongoing' => $ongoing->map($this->present())->values(),
            'soon' => $soon->map($this->present())->values(),
            'soonHours' => $soonHours,
            'totals' => [
                'ongoing' => $ongoing->count(),
                'soon' => $soon->count(),
                'sold' => (int) $ongoing->sum(fn (Event $event): int => (int) $event->tickets_count),
                'scanned' => (int) $ongoing->sum(fn (Event $event): int => (int) $event->scanned_tickets_count),
            ],
        ]);
    }

    /**
     * Les deux compteurs sont posés par `withCount` : une requête pour toute la
     * liste, au lieu d'un appel de statistiques par événement.
     *
     * @return Builder<Event>
     */
    private function baseQuery(?User $user): Builder
    {
        $query = Event::query()
            ->with(['venue', 'category', 'organizer'])
            ->where('status', EventStatus::PUBLISHED->value)
            ->withCount([
                'tickets',
                'tickets as scanned_tickets_count' => fn (Builder $q) => $q->whereNotNull('checked_in_at'),
            ]);

        // Un organisateur ne surveille que ses propres événements.
        if ($user !== null && ! $user->hasAnyRole(['admin', 'super-admin'])) {
            $organizerId = $user->organizer?->id;

            $organizerId === null
                ? $query->whereRaw('1 = 0')
                : $query->where('organizer_id', $organizerId);
        }

        return $query;
    }

    /**
     * Forme volontairement plate et restreinte : cet écran n'a besoin ni de la
     * description ni des types de billets, et `EventResource` sert le site
     * public — l'y étendre alourdirait chaque page du catalogue.
     */
    private function present(): callable
    {
        return static function (Event $event): array {
            $sold = (int) $event->tickets_count;
            $scanned = (int) $event->scanned_tickets_count;

            return [
                'id' => $event->id,
                'title' => $event->title,
                'status' => $event->status->value,
                'statusLabel' => $event->status->label(),
                'eventType' => $event->event_type->value,
                'startDate' => $event->start_date?->toIso8601String(),
                'endDate' => $event->end_date?->toIso8601String(),
                'thumbnail' => $event->thumbnail,
                'category' => $event->category?->name,
                'organizer' => $event->organizer?->company_name,
                'venue' => $event->venue === null ? null : [
                    'name' => $event->venue->name,
                    'city' => $event->venue->city,
                ],
                'maxAttendees' => $event->max_attendees,
                'sold' => $sold,
                'scanned' => $scanned,
                'notScanned' => max(0, $sold - $scanned),
                // Part des billets vendus déjà passés à l'entrée.
                'attendanceRate' => $sold === 0 ? 0 : (int) round($scanned / $sold * 100),
            ];
        };
    }
}
