<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\V1\Event\CreateEventAction;
use App\Actions\V1\Event\DeleteEventAction;
use App\Actions\V1\Event\PublishEventAction;
use App\Actions\V1\Event\UpdateEventAction;
use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Events\ResourceChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Support\CatalogueAudience;
use App\Support\CatalogueCache;
use App\Support\Commission;
use App\Http\Requests\V1\Event\StoreEventRequest;
use App\Http\Requests\V1\Event\UpdateEventRequest;
use App\Http\Resources\V1\EventResource;
use App\Models\Event;
use App\Models\Organizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;

/**
 * @group Events
 *
 * APIs for managing events
 */
final class EventController extends Controller
{
    /**
     * List all events
     *
     * @queryParam status Filter by status (draft, published, cancelled, finished). Example: published
     * @queryParam category_id Filter by category ULID. Example: 01HXE2K3M4N5P6Q7R8S9T0V1W3
     * @queryParam organizer_id Filter by organizer ULID. Example: 01HXE2K3M4N5P6Q7R8S9T0V1W2
     * @queryParam venue_id Filter by venue ULID. Example: 01HXE2K3M4N5P6Q7R8S9T0V1W4
     * @queryParam event_type Filter by type (physical, online). Example: physical
     * @queryParam search Match title, description, category or venue. Example: concert
     * @queryParam organizer_search Match the organizer company name. Example: Lomé Live
     * @queryParam city Match the venue city. Example: Lomé
     * @queryParam when Restrict to `upcoming` or `past` events. Example: upcoming
     * @queryParam starts_after Only events starting on or after this date. Example: 2026-08-01
     * @queryParam starts_before Only events starting on or before this date. Example: 2026-08-31
     * @queryParam max_price Only events with a ticket at or below this price. Example: 25000
     * @queryParam sort Ordering: recent, price-asc, price-desc, date-asc, date-desc. Example: price-asc
     * @queryParam per_page Items per page (1-50, default 15). Example: 9
     *
     * @response 200 {
     *   "success": true,
     *   "message": "",
     *   "data": [
     *     {
     *       "id": "01HXE2K3M4N5P6Q7R8S9T0V1W5",
     *       "organizerId": "01HXE2K3M4N5P6Q7R8S9T0V1W2",
     *       "categoryId": "01HXE2K3M4N5P6Q7R8S9T0V1W3",
     *       "venueId": "01HXE2K3M4N5P6Q7R8S9T0V1W4",
     *       "title": "Summer Music Festival",
     *       "slug": "summer-music-festival-2024",
     *       "description": "Amazing event...",
     *       "banner": "https://example.com/banner.jpg",
     *       "startDate": "2024-06-15T18:00:00+00:00",
     *       "endDate": "2024-06-15T23:00:00+00:00",
     *       "maxAttendees": 1000,
     *       "status": "published",
     *       "statusLabel": "Publié",
     *       "ticketTypesCount": 3,
     *       "reviewsCount": 5,
     *       "averageRating": 4.5,
     *       "createdAt": "2024-01-15T10:00:00.000000Z",
     *       "updatedAt": "2024-01-15T10:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $payload = CatalogueCache::remember(
            'events',
            $request,
            CatalogueCache::EVENTS_TTL,
            fn (): array => $this->buildIndex($request)->response()->getData(true),
        );

        // Returned raw so `NormalizeApiResponse` wraps it exactly as it wraps a
        // live resource collection: a cached response is indistinguishable.
        return response()->json($payload);
    }

    /**
     * The catalogue query behind {@see index()}.
     *
     * Split out so the caching above reads as caching, and this reads as the
     * filtering it is.
     */
    private function buildIndex(Request $request): AnonymousResourceCollection
    {
        $query = Event::query()
            // `ticketTypes` is eager-loaded, not just counted: the cards show
            // "à partir de X" and grey out a sold-out event, and both are
            // derived from the ticket types. Without them every card read
            // "Indisponible" and "Complet" — `[].every()` is true.
            ->with(['category', 'venue', 'organizer', 'ticketTypes'])
            ->withCount(['ticketTypes', 'favoritedBy']);

        // Côté public — visiteur *et* participant connecté : on ne montre que ce
        // qui est encore à venir, et seulement des organisateurs actifs. Le
        // back-office voit tout : un organisateur doit pouvoir revenir sur ses
        // événements passés, et les rapports agrègent dessus.
        //
        // Le test portait sur « anonyme » et non sur « public », ce qui laissait
        // un participant connecté voir les événements d'un organisateur
        // désactivé — alors que la désactivation existe précisément pour les
        // retirer du côté client.
        if (! CatalogueAudience::requestSeesEverything($request)) {
            // Le site public ne montre QUE des événements publiés : un brouillon
            // ou un événement annulé n'a rien à faire dans le catalogue que
            // visitent les gens. Le back-office, lui, voit tous les statuts (il
            // faut bien pouvoir travailler sur un brouillon).
            $query->where('status', EventStatus::PUBLISHED->value);

            $query->whereHas('organizer', function (Builder $q): void {
                $q->where('is_active', true);
            });

            $this->hidePastEvents($query);
        }

        // Cloisonnement du back-office : un organisateur ne voit que ses propres
        // événements, quel que soit le filtre `organizer_id` qu'il envoie. Sans
        // cette borne, `screen.events` lui ouvrait tout le catalogue des
        // confrères. L'administration, elle, n'est pas bornée (voir
        // {@see CatalogueAudience::scopedOrganizerId()}).
        $scopedOrganizerId = CatalogueAudience::scopedOrganizerId($request);

        if ($scopedOrganizerId !== null) {
            $query->where('organizer_id', $scopedOrganizerId);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('organizer_id')) {
            $query->where('organizer_id', $request->input('organizer_id'));
        }

        if ($request->filled('venue_id')) {
            $query->where('venue_id', $request->input('venue_id'));
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->input('event_type'));
        }

        // Free-text search for the public catalogue: title, description,
        // category, venue and organizer. A visitor types "Lomé", "concert" or
        // a promoter's name as readily as a title, and the search box is the
        // one place where all of those should work.
        if ($request->filled('search')) {
            $search = (string) $request->input('search');

            $query->where(function (Builder $q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('category', fn (Builder $c) => $c->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%"))
                    ->orWhereHas('venue', fn (Builder $v) => $v->where('city', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%"))
                    ->orWhereHas('organizer', fn (Builder $o) => $o->where('company_name', 'like', "%{$search}%"));
            });
        }

        // Separate from `search` on purpose. The header's quick search must not
        // surface every event of an agency whose name happens to contain the
        // term; the explore page's own "Organisateur" filter is where someone
        // deliberately looks a promoter up.
        if ($request->filled('organizer_search')) {
            $name = (string) $request->input('organizer_search');

            $query->whereHas('organizer', fn (Builder $o) => $o->where('company_name', 'like', "%{$name}%"));
        }

        if ($request->filled('city')) {
            $city = (string) $request->input('city');

            $query->whereHas('venue', fn (Builder $v) => $v->where('city', 'like', "%{$city}%"));
        }

        // `upcoming` / `past` are decided on the end date: an event that started
        // yesterday and runs for three days is still upcoming to a buyer.
        $when = $request->input('when');

        if ($when === 'upcoming') {
            $query->where(function (Builder $q): void {
                $q->where('end_date', '>=', now())->orWhereNull('end_date');
            });
        } elseif ($when === 'past') {
            $query->where('end_date', '<', now());
        }

        // Date window behind the "ce week-end / la semaine prochaine / ce
        // mois-ci" filters. Both bounds are inclusive and applied to the start.
        if ($request->filled('starts_after')) {
            $query->where('start_date', '>=', $request->date('starts_after'));
        }

        if ($request->filled('starts_before')) {
            $query->where('start_date', '<=', $request->date('starts_before'));
        }

        // Price ceiling: keep events with at least one ticket type at or below
        // it, using the same effective price as the sort below.
        //
        // The bound parameter is CAST explicitly because PDO sends it as a
        // string: SQLite then applies its type-ordering rule, under which every
        // number compares as smaller than every text value, and the filter
        // silently matches everything. The cast keeps the comparison numeric on
        // both SQLite (tests) and MySQL (production).
        if ($request->filled('max_price')) {
            $ceiling = (float) $request->input('max_price');

            $query->whereHas('ticketTypes', function (Builder $q) use ($ceiling): void {
                $q->whereRaw(
                    '(CASE WHEN promotional_price IS NOT NULL'
                    .' AND (promotion_start_date IS NULL OR promotion_start_date <= ?)'
                    .' AND (promotion_end_date IS NULL OR promotion_end_date >= ?)'
                    .' THEN promotional_price ELSE price END) <= CAST(? AS DECIMAL(12,2))',
                    [now(), now(), $ceiling],
                );
            });
        }

        $this->applySort($query, (string) $request->input('sort', 'recent'));

        $perPage = (int) $request->input('per_page', 15);
        $events = $query->paginate(max(1, min($perPage, 50)));

        return EventResource::collection($events);
    }

    /**
     * Un événement que le côté public ne doit pas rendre, même par son URL.
     *
     * Les deux mêmes conditions que la liste, écrites en PHP parce qu'ici
     * l'événement est déjà chargé. Elles doivent rester alignées : un événement
     * absent de la liste mais servi par son lien direct, c'est le lien qu'on
     * partage sur WhatsApp qui contourne la règle.
     */
    private function isHiddenFromPublic(Event $event): bool
    {
        // Seul un événement publié est visible du public : un brouillon ou un
        // événement annulé servi par son lien direct contournerait la liste, qui
        // les cache déjà. Testé en premier, c'est gratuit (déjà sur le modèle).
        if ($event->status !== EventStatus::PUBLISHED) {
            return true;
        }

        // La date est déjà sur le modèle, donc gratuite : on la teste d'abord et
        // on sort avant de toucher la base. L'organisateur, lui, coûte une
        // lecture par clé primaire, et seulement pour un événement encore à
        // venir consulté par le public.
        if ($event->end_date !== null && $event->end_date->isPast()) {
            return true;
        }

        $event->loadMissing('organizer');

        return $event->organizer?->is_active === false;
    }

    /**
     * Ne garde que les événements qui ne sont pas terminés.
     *
     * La borne est la date de **fin**, jamais celle de début : un festival
     * commencé hier et qui court trois jours est encore à venir pour un
     * acheteur, et le retirer du catalogue au premier soir couperait la vente en
     * pleine exploitation.
     *
     * Un événement sans date de fin est conservé : l'absence de date n'est pas
     * une preuve qu'il est passé, et le faire disparaître serait un effet de
     * bord silencieux d'une donnée manquante.
     *
     * @param  Builder<Event>  $query
     */
    private function hidePastEvents(Builder $query): void
    {
        $query->where(function (Builder $q): void {
            $q->where('end_date', '>=', now())->orWhereNull('end_date');
        });
    }

    /**
     * Order the catalogue.
     *
     * Sorting by price orders on the cheapest ticket type of each event, which
     * is the figure the card shows ("à partir de X"). The effective price is
     * recomputed in SQL rather than read from a column because a promotional
     * price only counts while its window is open — the same rule as
     * {@see TicketType::currentPrice()}.
     *
     * @param  Builder<Event>  $query
     */
    private function applySort(Builder $query, string $sort): void
    {
        if ($sort === 'price-asc' || $sort === 'price-desc') {
            $query->orderBy(
                TicketType::query()
                    ->selectRaw('MIN(CASE WHEN promotional_price IS NOT NULL'
                        .' AND (promotion_start_date IS NULL OR promotion_start_date <= ?)'
                        .' AND (promotion_end_date IS NULL OR promotion_end_date >= ?)'
                        .' THEN promotional_price ELSE price END)', [now(), now()])
                    ->whereColumn('ticket_types.event_id', 'events.id'),
                $sort === 'price-asc' ? 'asc' : 'desc',
            );

            return;
        }

        match ($sort) {
            'date-asc' => $query->orderBy('start_date'),
            'date-desc' => $query->orderByDesc('start_date'),
            default => $query->latest(),
        };
    }

    /**
     * Store Event
     *
     * Store a newly created resource in storage.
     *
     * @header Accept-Language en
     *
     * @response 201 scenario="Created" {
     *   "message": "Event created successfully",
     *   "data": {
     *     "id": "01jkp5zz...",
     *     "title": "Summer Music Festival"
     *   }
     * }
     */
    public function store(StoreEventRequest $request, CreateEventAction $action): JsonResponse
    {
        /** @var array{organizer_id: string, category_id: string, venue_id?: string|null, title: string, slug: string, description: string, banner?: UploadedFile|null, start_date: string, end_date: string, max_attendees?: int|null, is_featured?: bool, location_type?: string} $validated */
        $validated = $request->validated();

        // Un organisateur ne crée que pour lui-même : la policy confronte le
        // compte au propriétaire de l'organisateur ciblé. L'administration passe
        // (bypass). Sans cela, la route n'exigeait qu'un jeton — n'importe quel
        // compte connecté pouvait créer un événement au nom d'un organisateur.
        $this->authorize('create', [Event::class, Organizer::find($validated['organizer_id'])]);

        $event = $action->execute($validated);

        ResourceChangedEvent::dispatchQuietly('events', 'created', $event->id, $event->title);

        return $this->created(new EventResource($event));
    }

    /**
     * Show a single event
     *
     * @response 200 {
     *   "success": true,
     *   "message": "",
     *   "data": {
     *     "id": "01HXE2K3M4N5P6Q7R8S9T0V1W5",
     *     "organizerId": "01HXE2K3M4N5P6Q7R8S9T0V1W2",
     *     "categoryId": "01HXE2K3M4N5P6Q7R8S9T0V1W3",
     *     "venueId": "01HXE2K3M4N5P6Q7R8S9T0V1W4",
     *     "title": "Summer Music Festival",
     *     "slug": "summer-music-festival-2024",
     *     "description": "Join us for an amazing night...",
     *     "banner": "https://example.com/banner.jpg",
     *     "startDate": "2024-06-15T18:00:00+00:00",
     *     "endDate": "2024-06-15T23:00:00+00:00",
     *     "maxAttendees": 1000,
     *     "status": "published",
     *     "statusLabel": "Publié",
     *     "organizer": {...},
     *     "category": {...},
     *     "venue": {...},
     *     "ticketTypes": [...]
     *   }
     * }
     */
    public function show(Request $request, Event $id): JsonResponse
    {
        // Une URL n'est pas une autorisation. Le côté public ne montre pas les
        // événements terminés, et cette page était la porte de service : le lien
        // d'un concert de l'an dernier continuait de rendre l'affiche, les
        // tarifs et le bouton de réservation.
        //
        // 404 et non 403 : « cet événement n'est pas visible ici » n'a pas à
        // révéler qu'il existe. Le back-office, lui, passe.
        if (! CatalogueAudience::requestSeesEverything($request) && $this->isHiddenFromPublic($id)) {
            abort(404);
        }

        // Un organisateur n'ouvre pas la fiche d'un confrère par son URL : la
        // page d'édition du back-office s'appuie sur cette même route, et 404 —
        // « pas visible ici » — n'a pas à révéler que l'événement existe.
        $scopedOrganizerId = CatalogueAudience::scopedOrganizerId($request);

        if ($scopedOrganizerId !== null && (string) $id->organizer_id !== $scopedOrganizerId) {
            abort(404);
        }

        // Cached like the listing, and for the same short window: an event page
        // is the most-hit URL on the site once a link circulates, and its five
        // eager-loaded relations make it the most expensive to build. 60s keeps
        // "dernières places" honest.
        $payload = CatalogueCache::remember(
            'event:'.$id->id,
            $request,
            CatalogueCache::EVENTS_TTL,
            static function () use ($id): array {
                $id->load(['organizer', 'category', 'venue', 'ticketTypes', 'occurrences'])
                    ->loadCount(['ticketTypes', 'favoritedBy']);

                return (new EventResource($id))->response()->getData(true);
            },
        );

        return $this->success(
            data: $payload['data'] ?? $payload,
        );
    }

    /**
     * Update Event
     *
     * Update the specified resource in storage.
     *
     * @header Accept-Language en
     *
     * @urlParam event string required The ID of the event (ULID)
     *
     * @response 200 scenario="Updated" {
     *   "message": "Event updated successfully",
     *   "data": {
     *     "id": "01jkp5zz...",
     *     "title": "Summer Festival Updated"
     *   }
     * }
     */
    public function update(UpdateEventRequest $request, Event $id, UpdateEventAction $action): JsonResponse
    {
        $this->authorize('update', $id);

        /** @var array{category_id?: string, venue_id?: string|null, title?: string, slug?: string, description?: string, banner?: UploadedFile|null, start_date?: string, end_date?: string, max_attendees?: int|null, is_featured?: bool, location_type?: string} $validated */
        $validated = $request->validated();

        $data = [
            'event' => $id,
            ...$validated,
        ];

        $updated = $action->execute($data);

        ResourceChangedEvent::dispatchQuietly('events', 'updated', $updated->id, $updated->title);

        return $this->success(new EventResource($updated));
    }

    /**
     * Delete Event
     *
     * Delete the specified resource from storage.
     *
     * @header Accept-Language en
     *
     * @urlParam event string required The ID of the event (ULID)
     *
     * @response 204 scenario="Deleted"
     */
    public function destroy(Event $id, DeleteEventAction $action): JsonResponse
    {
        $this->authorize('delete', $id);

        $eventId = $id->id;
        $action->execute(['event' => $id]);

        ResourceChangedEvent::dispatchQuietly('events', 'deleted', $eventId);

        return $this->noContent();
    }

    /**
     * Publish Event
     *
     * Publish a draft event.
     *
     * @header Accept-Language en
     *
     * @urlParam event string required The ID of the event (ULID)
     *
     * @response 200 scenario="Published" {
     *   "message": "Event published successfully",
     *   "data": {
     *     "id": "01jkp5zz...",
     *     "status": "published"
     *   }
     * }
     */
    public function publish(Event $id, PublishEventAction $action): JsonResponse
    {
        $this->authorize('update', $id);

        try {
            $published = $action->execute(['event' => $id]);

            ResourceChangedEvent::dispatchQuietly('events', 'updated', $published->id, $published->title);

            return $this->success(new EventResource($published));
        } catch (\DomainException $e) {
            return $this->error(
                message: $e->getMessage(),
                status: 422,
            );
        }
    }

    /**
     * Unpublish an event
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Événement dépublié avec succès.",
     *   "data": {
     *     "id": "01HXE2K3M4N5P6Q7R8S9T0V1W5",
     *     "status": "draft",
     *     "statusLabel": "Brouillon"
     *   }
     * }
     */
    public function unpublish(Event $id): JsonResponse
    {
        $this->authorize('update', $id);

        $id->update(['status' => EventStatus::DRAFT]);

        ResourceChangedEvent::dispatchQuietly('events', 'updated', $id->id, $id->title);

        return $this->success(
            data: new EventResource($id),
            message: 'Événement dépublié avec succès.',
        );
    }

    /**
     * Cancel an event
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Événement annulé avec succès.",
     *   "data": {
     *     "id": "01HXE2K3M4N5P6Q7R8S9T0V1W5",
     *     "status": "cancelled",
     *     "statusLabel": "Annulé"
     *   }
     * }
     */
    public function cancel(Event $id): JsonResponse
    {
        $this->authorize('update', $id);

        $id->update(['status' => EventStatus::CANCELLED]);

        ResourceChangedEvent::dispatchQuietly('events', 'updated', $id->id, $id->title);

        return $this->success(
            data: new EventResource($id),
            message: 'Événement annulé avec succès.',
        );
    }

    /**
     * Event box-office report
     *
     * Per ticket type: max quantity, sold, scanned (checked-in), not scanned,
     * not sold, revenue and the TicketExpress commission — plus event totals.
     *
     * Un organisateur ne lit que la billetterie de ses propres événements. La
     * route était ouverte à tout organisateur : le chiffre d'affaires d'un
     * confrère se lisait avec son seul identifiant d'événement.
     *
     * @urlParam event string required The ID of the event (ULID)
     *
     * @response 403 {
     *   "success": false,
     *   "message": "Vous ne pouvez consulter que la billetterie de vos propres événements."
     * }
     */
    public function stats(Request $request, Event $id): JsonResponse
    {
        $user = $request->user();

        if ($user !== null
            && ! $user->hasAnyRole(['admin', 'super-admin'])
            && (string) ($user->organizer?->id) !== (string) $id->organizer_id) {
            return $this->error(
                message: 'Vous ne pouvez consulter que la billetterie de vos propres événements.',
                status: 403,
            );
        }

        $ticketTypes = $id->ticketTypes()->get();

        $rows = $ticketTypes->map(function (TicketType $tt): array {
            $sold = Ticket::query()->where('ticket_type_id', $tt->id)->count();
            $scanned = Ticket::query()->where('ticket_type_id', $tt->id)->whereNotNull('checked_in_at')->count();
            $revenue = (float) OrderItem::query()
                ->where('ticket_type_id', $tt->id)
                ->whereHas('order', fn ($q) => $q->where('status', OrderStatus::PAID->value))
                ->sum('subtotal');

            return [
                'ticketTypeId' => $tt->id,
                'name' => $tt->name,
                'price' => (float) $tt->price,
                'quantity' => (int) $tt->quantity,
                'sold' => $sold,
                'scanned' => $scanned,
                'notScanned' => max(0, $sold - $scanned),
                'notSold' => max(0, (int) $tt->quantity - $sold),
                'revenue' => round($revenue, 2),
                'commission' => Commission::amountFor($revenue),
                'netRevenue' => Commission::netFor($revenue),
            ];
        })->values();

        return $this->success([
            'event' => [
                'id' => $id->id,
                'title' => $id->title,
                'maxAttendees' => $id->max_attendees,
            ],
            'commissionRate' => Commission::rate(),
            'ticketTypes' => $rows,
            'totals' => [
                'quantity' => (int) $rows->sum('quantity'),
                'sold' => (int) $rows->sum('sold'),
                'scanned' => (int) $rows->sum('scanned'),
                'notScanned' => (int) $rows->sum('notScanned'),
                'notSold' => (int) $rows->sum('notSold'),
                'revenue' => round((float) $rows->sum('revenue'), 2),
                'commission' => round((float) $rows->sum('commission'), 2),
                'netRevenue' => round((float) $rows->sum('netRevenue'), 2),
            ],
        ]);
    }
}
