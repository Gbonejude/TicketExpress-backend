<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use App\Support\PersonSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Participants
 *
 * Les acheteurs de billets, vus depuis le back-office.
 *
 * Distinct de « Gestion des Utilisateurs », qui administre des comptes (rôle,
 * mot de passe, photo). Ici on regarde un participant sous l'angle de ce qu'il a
 * acheté : combien de billets, pour quel montant, sur quels événements, et où il
 * en est de ses entrées. Ce sont deux questions différentes sur les mêmes lignes.
 *
 * @authenticated
 */
final class ParticipantController extends Controller
{
    /**
     * List participants
     *
     * @queryParam search string Match name, email or phone. Example: Komi
     * @queryParam sort string `recent` (default), `spent`, `tickets`. Example: spent
     * @queryParam per_page int Items per page (1-100, default 15). Example: 100
     *
     * @response 200 {
     *   "success": true,
     *   "message": "",
     *   "data": [
     *     {
     *       "id": "01HXE2K3M4N5P6Q7R8S9T0V1W1",
     *       "fullName": "Komi CREPPY",
     *       "phone": "+22890510465",
     *       "paidOrders": 3,
     *       "tickets": 5,
     *       "totalSpent": 75000
     *     }
     *   ]
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query()
            ->whereHas('roles', fn (Builder $q) => $q->where('name', UserRole::PARTICIPANT->value));

        // Mot par mot : un nom complet tapé d'un trait doit trouver la personne.
        PersonSearch::apply(
            $query,
            $request->input('search'),
            ['first_name', 'last_name', 'email', 'phone'],
        );

        // Les agrégats sont calculés en SQL, pas en chargeant les commandes de
        // chaque participant : une liste de 15 lignes ne doit pas ramener des
        // milliers de billets pour en compter la somme.
        $paid = OrderStatus::PAID->value;

        $query
            ->withCount([
                'orders as paid_orders_count' => fn (Builder $q) => $q->where('status', $paid),
            ])
            ->withSum(
                ['orders as total_spent' => fn (Builder $q) => $q->where('status', $paid)],
                'total_amount',
            );

        $query = match ($request->input('sort')) {
            'spent' => $query->orderByDesc('total_spent'),
            'tickets' => $query->orderByDesc('paid_orders_count'),
            default => $query->latest(),
        };

        // `per_page` borné à 100 : les listes déroulantes du back-office (filtre
        // participant des rapports) doivent contenir tout le monde, pas les 15
        // premiers.
        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);

        $participants = $query->paginate($perPage);

        // Le nombre de billets se compte sur `tickets`, pas sur les commandes :
        // une commande peut en contenir plusieurs. Une seule requête groupée pour
        // toute la page.
        $ticketCounts = $this->ticketCountsFor($participants->pluck('id')->all());

        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $participants->getCollection()
                ->map(fn (User $user): array => $this->present($user, $ticketCounts))
                ->values(),
            'meta' => [
                'current_page' => $participants->currentPage(),
                'per_page' => $participants->perPage(),
                'total' => $participants->total(),
                'last_page' => $participants->lastPage(),
            ],
        ]);
    }

    /**
     * Show one participant with their purchases
     *
     * @urlParam participant string required The user ULID.
     */
    public function show(string $id): JsonResponse
    {
        $user = User::query()
            ->whereHas('roles', fn (Builder $q) => $q->where('name', UserRole::PARTICIPANT->value))
            ->findOrFail($id);

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->with(['items.ticketType.event'])
            ->withCount('tickets')
            ->latest()
            ->get();

        $tickets = Ticket::query()
            ->whereHas('order', fn (Builder $q) => $q->where('user_id', $user->id))
            ->with(['ticketType.event'])
            ->latest()
            ->limit(50)
            ->get();

        $paidOrders = $orders->where('status', OrderStatus::PAID);

        return $this->success([
            'participant' => [
                'id' => $user->id,
                'fullName' => $user->first_name.' '.$user->last_name,
                'firstName' => $user->first_name,
                'lastName' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'gender' => $user->gender?->value,
                'birthday' => $user->birthday,
                'image' => $user->getFirstMediaUrl('users'),
                'thumbnail' => $user->getFirstMediaUrl('users', 'thumb'),
                'createdAt' => $user->created_at?->toIso8601String(),
            ],
            'totals' => [
                'orders' => $orders->count(),
                'paidOrders' => $paidOrders->count(),
                'tickets' => (int) $paidOrders->sum('tickets_count'),
                'totalSpent' => (float) $paidOrders->sum('total_amount'),
                'checkedIn' => $tickets->whereNotNull('checked_in_at')->count(),
            ],
            'orders' => $orders->map(static fn (Order $order): array => [
                'id' => $order->id,
                'orderNumber' => $order->order_number,
                'status' => $order->status->value,
                'statusLabel' => $order->status->label(),
                'totalAmount' => $order->total_amount,
                'ticketsCount' => (int) $order->tickets_count,
                'events' => $order->items
                    ->map(fn ($item) => $item->ticketType?->event?->title)
                    ->filter()
                    ->unique()
                    ->values(),
                'createdAt' => $order->created_at?->toIso8601String(),
            ])->values(),
            'tickets' => $tickets->map(static fn (Ticket $ticket): array => [
                'id' => $ticket->id,
                'ticketNumber' => $ticket->ticket_number,
                'event' => $ticket->ticketType?->event?->title,
                'ticketType' => $ticket->ticketType?->name,
                'status' => $ticket->status->value,
                'statusLabel' => $ticket->status->label(),
                'checkedInAt' => $ticket->checked_in_at?->toIso8601String(),
            ])->values(),
        ]);
    }

    /**
     * Nombre de billets émis par participant, en une requête.
     *
     * @param  array<int, string>  $userIds
     * @return array<string, int>
     */
    private function ticketCountsFor(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        return Ticket::query()
            ->join('orders', 'orders.id', '=', 'tickets.order_id')
            ->whereIn('orders.user_id', $userIds)
            ->where('orders.status', OrderStatus::PAID->value)
            ->groupBy('orders.user_id')
            ->selectRaw('orders.user_id, COUNT(*) as total')
            ->pluck('total', 'orders.user_id')
            ->map(static fn ($total): int => (int) $total)
            ->all();
    }

    /**
     * @param  array<string, int>  $ticketCounts
     * @return array<string, mixed>
     */
    private function present(User $user, array $ticketCounts): array
    {
        return [
            'id' => $user->id,
            'fullName' => $user->first_name.' '.$user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'image' => $user->getFirstMediaUrl('users'),
            'thumbnail' => $user->getFirstMediaUrl('users', 'thumb'),
            'paidOrders' => (int) ($user->paid_orders_count ?? 0),
            'tickets' => $ticketCounts[$user->id] ?? 0,
            'totalSpent' => (float) ($user->total_spent ?? 0),
            'createdAt' => $user->created_at?->toIso8601String(),
        ];
    }
}
