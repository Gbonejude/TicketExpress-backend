<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Organizer;
use App\Models\Ticket;
use App\Models\User;
use App\Support\Commission;
use App\Support\ReportPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Reports
 *
 * Rapports et statistiques de billetterie.
 *
 * Un seul jeu de filtres — période, organisateur, événement, participant — sert
 * les trois sorties : les compteurs, le journal des commandes, et le PDF. C'est
 * volontaire : un rapport imprimé doit contenir exactement ce que l'écran
 * affichait, sinon il ne prouve rien.
 *
 * Tout est calculé en SQL sur les commandes **payées** : une commande en attente
 * n'est pas un revenu, et une annulée n'en a jamais été un.
 *
 * @authenticated
 */
final class ReportController extends Controller
{
    /**
     * Report overview
     *
     * @queryParam filterType string all, range, year, month. Example: month
     * @queryParam startDate date Start of the range. Example: 2026-08-01
     * @queryParam endDate date End of the range. Example: 2026-08-31
     * @queryParam year int Year for the year/month filters. Example: 2026
     * @queryParam month int Month (1-12) for the month filter. Example: 8
     * @queryParam organizer_id string Restrict to one organizer (admin only). No-example
     * @queryParam event_id string Restrict to one event. No-example
     * @queryParam participant_id string Restrict to one participant. No-example
     */
    public function overview(Request $request): JsonResponse
    {
        $period = ReportPeriod::fromRequest($request);

        $paidRevenue = (float) $this->itemQuery($request, $period, paidOnly: true)->sum('order_items.subtotal');

        return $this->success([
            'period' => [
                'type' => $period->type,
                'label' => $period->label,
                'from' => $period->from?->toDateString(),
                'to' => $period->to?->toDateString(),
            ],
            'filters' => $this->describeFilters($request),
            'totals' => [
                'revenue' => round($paidRevenue, 2),
                'commissionRate' => Commission::rate(),
                'commission' => Commission::amountFor($paidRevenue),
                'netRevenue' => Commission::netFor($paidRevenue),
                'orders' => $this->orderQuery($request, $period)->count(),
                'paidOrders' => $this->orderQuery($request, $period, paidOnly: true)->count(),
                'ticketsSold' => (int) $this->itemQuery($request, $period, paidOnly: true)->sum('order_items.quantity'),
                'ticketsIssued' => $this->ticketQuery($request, $period)->count(),
                'checkedIn' => $this->ticketQuery($request, $period)->whereNotNull('tickets.checked_in_at')->count(),

                // Les billets annulés sont comptés à part et restent dans
                // `ticketsIssued` : ils ont bien été émis, c'est justement ce qui
                // rend leur annulation lisible. Les soustraire ferait disparaître
                // l'information au lieu de la montrer.
                'ticketsCancelled' => $this->ticketQuery($request, $period)
                    ->where('tickets.status', TicketStatus::CANCELLED->value)
                    ->count(),
                'participants' => $this->orderQuery($request, $period, paidOnly: true)
                    ->distinct()
                    ->count('orders.email'),
                'averageBasket' => $this->averageBasket($request, $period),
            ],
            'ordersByStatus' => $this->ordersByStatus($request, $period),
            'series' => $this->series($request, $period),
            'topEvents' => $this->topEvents($request, $period),
            'revenueByCategory' => $this->revenueByCategory($request, $period),
            'revenueByPaymentMethod' => $this->revenueByPaymentMethod($request, $period),
        ]);
    }

    /**
     * Platform totals
     *
     * Les compteurs de fond du tableau de bord : ce que la plateforme porte
     * depuis le début, sans période.
     *
     * Volontairement séparé de `overview`, qui est toujours lu à travers un mois
     * ou un intervalle : « 3 événements » et « 3 événements en août » ne sont pas
     * le même chiffre, et les mêler dans un seul bloc `totals` ferait lire l'un
     * pour l'autre. La portée organisateur, elle, s'applique aux deux.
     *
     * @queryParam organizer_id string Restrict to one organizer (admin only). No-example
     */
    public function platform(Request $request): JsonResponse
    {
        $organizerId = $this->effectiveOrganizerId($request);
        $now = now();

        /** @return Builder<Event> */
        $events = fn (): Builder => Event::query()
            ->when($organizerId !== null, fn (Builder $query) => $query->where('organizer_id', $organizerId));

        // Passé, à venir et annulé forment trois cases disjointes : un événement
        // annulé n'est pas « passé », même si sa date l'est. Ce qui a commencé et
        // n'est pas fini n'entre dans aucune des deux premières — c'est « en ce
        // moment », que la carte du bas porte déjà.
        $live = fn (): Builder => $events()->where('status', '!=', EventStatus::CANCELLED->value);

        return $this->success([
            // Un organisateur ne compte pas ses confrères : le chiffre n'a de sens
            // qu'à l'échelle de la plateforme, et l'écran masque la carte.
            'organizers' => $organizerId === null ? Organizer::query()->count() : null,
            'events' => $events()->count(),
            'cancelledEvents' => $events()->where('status', EventStatus::CANCELLED->value)->count(),
            'pastEvents' => $live()->where('end_date', '<', $now)->count(),
            'upcomingEvents' => $live()->where('start_date', '>', $now)->count(),

            // Même définition que `totals.participants` : les acheteurs distincts
            // d'une commande payée. Deux lectures du même mot ne peuvent donc pas
            // diverger d'un écran à l'autre.
            'participants' => $this->orderQuery($request, self::allTime(), paidOnly: true)
                ->distinct()
                ->count('orders.email'),
        ]);
    }

    /**
     * Report order journal
     *
     * Le détail ligne à ligne, paginé, sur les mêmes filtres que les compteurs.
     *
     * @queryParam status string Restrict to one order status. Example: paid
     * @queryParam per_page int 1-100, default 15. Example: 25
     */
    public function journal(Request $request): JsonResponse
    {
        $period = ReportPeriod::fromRequest($request);

        $query = $this->orderQuery($request, $period)
            ->with(['items.ticketType.event.organizer'])
            ->withCount('tickets')
            ->latest('orders.created_at');

        if ($request->filled('status')) {
            $query->where('orders.status', $request->input('status'));
        }

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);
        $orders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $orders->getCollection()->map($this->presentOrder())->values(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }

    /**
     * Export the report as PDF
     *
     * Le PDF reprend les compteurs, le palmarès des événements et le journal
     * (borné à 500 lignes : au-delà, le document n'est plus lisible et dompdf
     * s'épuise).
     */
    public function exportPdf(Request $request): Response
    {
        $period = ReportPeriod::fromRequest($request);

        $paidRevenue = (float) $this->itemQuery($request, $period, paidOnly: true)->sum('order_items.subtotal');

        $orders = $this->orderQuery($request, $period)
            ->with(['items.ticketType.event'])
            ->withCount('tickets')
            ->latest('orders.created_at')
            ->limit(500)
            ->get()
            ->map($this->presentOrder());

        $pdf = Pdf::loadView('reports.billetterie', [
            'period' => $period,
            'filters' => $this->describeFilters($request),
            'revenue' => $paidRevenue,
            'commission' => Commission::amountFor($paidRevenue),
            'netRevenue' => Commission::netFor($paidRevenue),
            'commissionRate' => Commission::rate(),
            'ordersCount' => $this->orderQuery($request, $period)->count(),
            'paidOrdersCount' => $this->orderQuery($request, $period, paidOnly: true)->count(),
            'ticketsSold' => (int) $this->itemQuery($request, $period, paidOnly: true)->sum('order_items.quantity'),
            'checkedIn' => $this->ticketQuery($request, $period)->whereNotNull('tickets.checked_in_at')->count(),
            'ordersByStatus' => $this->ordersByStatus($request, $period),
            'topEvents' => $this->topEvents($request, $period),
            'revenueByCategory' => $this->revenueByCategory($request, $period),
            'orders' => $orders,
            'truncated' => $orders->count() >= 500,
            'generatedAt' => now()->translatedFormat('d/m/Y à H:i'),
        ])->setPaper('a4', 'landscape');

        $name = sprintf('rapport-billetterie-%s.pdf', now()->format('Y-m-d-Hi'));

        return $pdf->download($name);
    }

    /* ─────────────────────────────────────────────────────────────────────────
     * Requêtes de base
     *
     * Trois portées, parce que les questions ne se posent pas au même niveau :
     * les commandes comptent les achats, les lignes portent le chiffre
     * d'affaires, les billets portent les entrées. Chacune applique les mêmes
     * filtres, d'où ces trois constructeurs plutôt qu'un seul mal partagé.
     * ────────────────────────────────────────────────────────────────────── */

    /** La période sans bornes, pour les compteurs qui n'en ont pas. */
    private static function allTime(): ReportPeriod
    {
        return new ReportPeriod('all', null, null, 'Depuis le début');
    }

    /** @return Builder<Order> */
    private function orderQuery(Request $request, ReportPeriod $period, bool $paidOnly = false): Builder
    {
        $query = Order::query();

        if ($period->isBounded()) {
            $query->whereBetween('orders.created_at', [$period->from, $period->to]);
        }

        if ($paidOnly) {
            $query->where('orders.status', OrderStatus::PAID->value);
        }

        if ($request->filled('participant_id')) {
            $query->where('orders.user_id', $request->input('participant_id'));
        }

        $this->constrainToScope($request, $query, 'orders.id');

        return $query;
    }

    /**
     * Les lignes de commande, jointes pour atteindre l'événement.
     *
     * @return Builder<OrderItem>
     */
    private function itemQuery(Request $request, ReportPeriod $period, bool $paidOnly = false): Builder
    {
        $query = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('ticket_types', 'ticket_types.id', '=', 'order_items.ticket_type_id')
            ->join('events', 'events.id', '=', 'ticket_types.event_id');

        if ($period->isBounded()) {
            $query->whereBetween('orders.created_at', [$period->from, $period->to]);
        }

        if ($paidOnly) {
            $query->where('orders.status', OrderStatus::PAID->value);
        }

        if ($request->filled('participant_id')) {
            $query->where('orders.user_id', $request->input('participant_id'));
        }

        $this->applyEventFilters($request, $query);

        return $query;
    }

    /** @return Builder<Ticket> */
    private function ticketQuery(Request $request, ReportPeriod $period): Builder
    {
        $query = Ticket::query()
            ->join('ticket_types', 'ticket_types.id', '=', 'tickets.ticket_type_id')
            ->join('events', 'events.id', '=', 'ticket_types.event_id')
            ->join('orders', 'orders.id', '=', 'tickets.order_id');

        if ($period->isBounded()) {
            $query->whereBetween('orders.created_at', [$period->from, $period->to]);
        }

        if ($request->filled('participant_id')) {
            $query->where('orders.user_id', $request->input('participant_id'));
        }

        $this->applyEventFilters($request, $query);

        return $query;
    }

    /**
     * Filtres organisateur / événement sur une requête déjà jointe à `events`.
     *
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     */
    private function applyEventFilters(Request $request, Builder $query): void
    {
        if ($request->filled('event_id')) {
            $query->where('events.id', $request->input('event_id'));
        }

        $organizerId = $this->effectiveOrganizerId($request);

        if ($organizerId !== null) {
            $query->where('events.organizer_id', $organizerId);
        }
    }

    /**
     * Même restriction, mais sur une requête de commandes — qui n'est pas jointe
     * aux événements. Le passage par `whereExists` évite de dupliquer les lignes
     * d'une commande qui contient plusieurs types de billets, ce qu'une jointure
     * ferait, et qui gonflerait le nombre de commandes.
     *
     * @param  Builder<Order>  $query
     */
    private function constrainToScope(Request $request, Builder $query, string $orderIdColumn): void
    {
        $organizerId = $this->effectiveOrganizerId($request);
        $eventId = $request->input('event_id');

        if ($organizerId === null && ($eventId === null || $eventId === '')) {
            return;
        }

        $query->whereExists(function ($sub) use ($organizerId, $eventId, $orderIdColumn): void {
            $sub->select(DB::raw(1))
                ->from('order_items')
                ->join('ticket_types', 'ticket_types.id', '=', 'order_items.ticket_type_id')
                ->join('events', 'events.id', '=', 'ticket_types.event_id')
                ->whereColumn('order_items.order_id', $orderIdColumn);

            if ($organizerId !== null) {
                $sub->where('events.organizer_id', $organizerId);
            }

            if ($eventId !== null && $eventId !== '') {
                $sub->where('events.id', $eventId);
            }
        });
    }

    /**
     * L'organisateur réellement appliqué.
     *
     * Un organisateur est borné au sien, quoi qu'il demande : ses recettes ne
     * sont pas les recettes de la plateforme, et celles d'un confrère ne le
     * concernent pas. L'administration choisit librement.
     */
    private function effectiveOrganizerId(Request $request): ?string
    {
        $user = $request->user();

        if ($user !== null && ! $user->hasAnyRole(['admin', 'super-admin'])) {
            return $user->organizer?->id ?? '__none__';
        }

        $requested = $request->input('organizer_id');

        return $requested === null || $requested === '' ? null : (string) $requested;
    }

    /* ─────────────────────────────────────────────────────────────────────────
     * Agrégats
     * ────────────────────────────────────────────────────────────────────── */

    /** @return array<string, array{label: string, count: int, amount: float}> */
    private function ordersByStatus(Request $request, ReportPeriod $period): array
    {
        $rows = $this->orderQuery($request, $period)
            ->reorder()
            ->selectRaw('orders.status, COUNT(*) as total, SUM(orders.total_amount) as amount')
            ->groupBy('orders.status')
            ->get()
            ->keyBy('status');

        $out = [];

        foreach (OrderStatus::cases() as $status) {
            $out[$status->value] = [
                'label' => $status->label(),
                'count' => (int) ($rows[$status->value]->total ?? 0),
                'amount' => (float) ($rows[$status->value]->amount ?? 0),
            ];
        }

        return $out;
    }

    /**
     * Chiffre d'affaires et commandes dans le temps.
     *
     * @return array<int, array{bucket: string, revenue: float, orders: int}>
     */
    private function series(Request $request, ReportPeriod $period): array
    {
        $format = $period->granularity() === 'month' ? '%Y-%m' : '%Y-%m-%d';

        // SQLite (tests) ne connaît pas DATE_FORMAT ; MySQL ne connaît pas
        // strftime. La base est interrogée pour savoir laquelle on sert.
        $expression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('".str_replace('%', '%', $format)."', orders.created_at)"
            : "DATE_FORMAT(orders.created_at, '{$format}')";

        return $this->orderQuery($request, $period, paidOnly: true)
            ->reorder()
            ->selectRaw("{$expression} as bucket, SUM(orders.total_amount) as revenue, COUNT(*) as orders_count")
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->map(static fn ($row): array => [
                'bucket' => (string) $row->bucket,
                'revenue' => (float) $row->revenue,
                'orders' => (int) $row->orders_count,
            ])
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function topEvents(Request $request, ReportPeriod $period): array
    {
        $rows = $this->itemQuery($request, $period, paidOnly: true)
            ->join('organizers', 'organizers.id', '=', 'events.organizer_id')
            ->selectRaw(
                'events.id as event_id, events.title as title, organizers.company_name as organizer, '.
                'SUM(order_items.subtotal) as revenue, SUM(order_items.quantity) as tickets'
            )
            ->groupBy('events.id', 'events.title', 'organizers.company_name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // Les affiches en une requête, sur les seuls événements du palmarès :
        // l'agrégat passe par du SQL brut, donc sans modèle ni accesseur média.
        $thumbnails = Event::query()
            ->whereIn('id', $rows->pluck('event_id'))
            ->get()
            ->mapWithKeys(static fn (Event $event): array => [$event->id => $event->thumbnail])
            ->all();

        return $rows
            ->map(static fn ($row): array => [
                'eventId' => $row->event_id,
                'title' => $row->title,
                'organizer' => $row->organizer,
                'thumbnail' => $thumbnails[$row->event_id] ?? null,
                'revenue' => (float) $row->revenue,
                'tickets' => (int) $row->tickets,
                'commission' => Commission::amountFor((float) $row->revenue),
            ])
            ->all();
    }

    /** @return array<int, array{name: string, revenue: float, tickets: int}> */
    private function revenueByCategory(Request $request, ReportPeriod $period): array
    {
        return $this->itemQuery($request, $period, paidOnly: true)
            ->join('event_categories', 'event_categories.id', '=', 'events.category_id')
            ->selectRaw(
                'event_categories.name as name, SUM(order_items.subtotal) as revenue, '.
                'SUM(order_items.quantity) as tickets'
            )
            ->groupBy('event_categories.name')
            ->orderByDesc('revenue')
            ->get()
            ->map(static fn ($row): array => [
                'name' => (string) $row->name,
                'revenue' => (float) $row->revenue,
                'tickets' => (int) $row->tickets,
            ])
            ->all();
    }

    /** @return array<int, array{method: string, revenue: float, orders: int}> */
    private function revenueByPaymentMethod(Request $request, ReportPeriod $period): array
    {
        return $this->orderQuery($request, $period, paidOnly: true)
            ->reorder()
            ->selectRaw('orders.payment_method as method, SUM(orders.total_amount) as revenue, COUNT(*) as orders_count')
            ->groupBy('orders.payment_method')
            ->orderByDesc('revenue')
            ->get()
            ->map(static fn ($row): array => [
                'method' => match ($row->method) {
                    'flooz' => 'Flooz',
                    'tmoney' => 'Mix by Yas',
                    null, '' => 'Non renseigné',
                    default => (string) $row->method,
                },
                'revenue' => (float) $row->revenue,
                'orders' => (int) $row->orders_count,
            ])
            ->all();
    }

    private function averageBasket(Request $request, ReportPeriod $period): float
    {
        $paid = $this->orderQuery($request, $period, paidOnly: true);
        $count = (clone $paid)->count();

        if ($count === 0) {
            return 0.0;
        }

        return round((float) $paid->sum('orders.total_amount') / $count, 2);
    }

    /**
     * Les filtres, en clair — pour l'en-tête de l'écran comme du PDF.
     *
     * @return array<string, string|null>
     */
    private function describeFilters(Request $request): array
    {
        $organizerId = $this->effectiveOrganizerId($request);

        $organizer = $organizerId === null || $organizerId === '__none__'
            ? null
            : Organizer::find($organizerId)?->company_name;

        $event = $request->filled('event_id')
            ? Event::find($request->input('event_id'))?->title
            : null;

        $participant = $request->filled('participant_id')
            ? User::find($request->input('participant_id'))
            : null;

        return [
            'organizer' => $organizer,
            'event' => $event,
            'participant' => $participant === null
                ? null
                : trim($participant->first_name.' '.$participant->last_name),
        ];
    }

    /**
     * Une ligne de journal : la commande, son événement, ce qu'elle a rapporté.
     */
    private function presentOrder(): callable
    {
        return static function (Order $order): array {
            $events = $order->items
                ->map(fn ($item) => $item->ticketType?->event?->title)
                ->filter()
                ->unique()
                ->values();

            return [
                'id' => $order->id,
                'orderNumber' => $order->order_number,
                'participant' => trim($order->first_name.' '.$order->last_name),
                'phone' => $order->phone,
                'events' => $events,
                'ticketsCount' => (int) ($order->tickets_count ?? 0),
                'quantity' => (int) $order->items->sum('quantity'),
                'totalAmount' => (float) $order->total_amount,
                'status' => $order->status->value,
                'statusLabel' => $order->status->label(),
                'paymentMethod' => $order->payment_method,
                'createdAt' => $order->created_at?->toIso8601String(),
            ];
        };
    }
}
