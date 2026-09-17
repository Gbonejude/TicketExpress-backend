<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\V1\Order\CancelOrderAction;
use App\Actions\V1\Order\CreateOrderAction;
use App\Actions\V1\Ticket\RefundTicketAction;
use App\Enums\OrderStatus;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Order\StoreOrderRequest;
use App\Http\Resources\V1\OrderResource;
use App\Models\Order;
use App\Support\CatalogueAudience;
use App\Support\PersonSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

/**
 * @group Orders
 *
 * APIs for managing orders (supports guest checkout)
 */
final class OrderController extends Controller
{
    public function __construct(
        private readonly CreateOrderAction $createOrderAction,
        private readonly CancelOrderAction $cancelOrderAction,
        private readonly RefundTicketAction $refundTicketAction,
    ) {}

    /**
     * List orders
     *
     * For authenticated users, returns their orders.
     * For admins, returns all orders.
     *
     * @queryParam status Filter by status (pending, paid, cancelled, refunded). Example: paid
     * @queryParam email Filter by buyer email. Example: john@example.com
     * @queryParam event_id Keep only the orders containing a ticket of this event. Example: 01HXE2K3M4N5P6Q7R8S9T0V1W1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "",
     *   "data": [
     *     {
     *       "id": "01HXE2K3M4N5P6Q7R8S9T0V1W7",
     *       "userId": null,
     *       "firstName": "John",
     *       "lastName": "Doe",
     *       "fullName": "John Doe",
     *       "email": "john@example.com",
     *       "phone": "+22890123456",
     *       "totalAmount": 100000,
     *       "status": "pending",
     *       "statusLabel": "En attente",
     *       "paymentMethod": "stripe",
     *       "deliveryMethod": "email",
     *       "deliveryMethodLabel": "Email",
     *       "itemsCount": 2,
     *       "ticketsCount": 0,
     *       "createdAt": "2024-01-15T10:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Order::query()
            // The event is reached through the ticket type. Loading it here is
            // what lets "my orders" and "my tickets" name the event they belong
            // to without one extra request per row.
            ->with([
                'items.ticketType.event.venue',
                'tickets.ticketType.event.venue',
                'downloadLink',
                // La référence de transaction rapproche le SMS PayGate
                // (« Ref: … ») de la commande, côté client comme back-office.
                'payments',
                // Pour le portrait du participant dans la liste du back-office.
                // Null sur un achat invité, que la vue sait afficher.
                'user',
            ])
            ->withCount(['items', 'tickets'])
            ->latest();

        // Trois publics, trois portées. L'administration voit toutes les
        // commandes ; un organisateur, celles qui portent un billet de l'un de
        // ses événements (l'écran « Réservations » du back-office) ; un
        // participant, seulement les siennes en tant qu'acheteur.
        //
        // L'organisateur n'est donc PAS filtré par `user_id` : ce sont les
        // réservations de ses événements qui l'intéressent, pas ses propres
        // achats.
        $user = $request->user();
        $scopedOrganizerId = CatalogueAudience::scopedOrganizerId($request);
        $mine = $request->boolean('mine') || $request->input('scope') === 'me';

        if ($mine || ! ($user && $user->hasAnyRole(['admin', 'super-admin']) || $scopedOrganizerId !== null)) {
            $query->where(function (Builder $q) use ($user): void {
                $q->where('user_id', $user?->id);
                if ($user?->email) {
                    $q->orWhere('email', $user->email);
                }
            });
        } elseif ($scopedOrganizerId !== null) {
            $query->whereHas('items.ticketType.event', fn (Builder $q) => $q->where('organizer_id', $scopedOrganizerId));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('email')) {
            $query->where('email', $request->input('email'));
        }

        // Filtre par événement. `whereHas` plutôt qu'une jointure : une commande
        // qui contient deux types de billets du même événement apparaîtrait deux
        // fois avec une jointure, et le total de la pagination serait faux.
        if ($request->filled('event_id')) {
            $eventId = (string) $request->input('event_id');

            $query->whereHas('items.ticketType', function (Builder $q) use ($eventId): void {
                $q->where('event_id', $eventId);
            });
        }

        if ($request->filled('search')) {
            $search = (string) $request->input('search');

            // Mot par mot : « Komi CREPPY » cherche le prénom et le nom, qui
            // vivent dans deux colonnes.
            PersonSearch::apply(
                $query,
                $search,
                ['order_number', 'email', 'first_name', 'last_name'],
            );
        }

        // Agrégats globaux sur les commandes PAYÉES — calculés AVANT la
        // pagination pour refléter l'ensemble de l'historique, pas seulement
        // la page courante. On clone le builder pour conserver tous les
        // filtres (scope utilisateur, statut, etc.) sans les eager-loads
        // qui n'ont aucune utilité pour un agrégat.
        $paidQuery = (clone $query)->withoutEagerLoads()->where('status', 'paid');

        $totalSpent   = (float) (clone $paidQuery)->sum('total_amount');
        $totalTickets = (int)   \DB::table('tickets')
            ->whereIn('order_id', (clone $paidQuery)->select('id'))
            ->count();

        $orders = $query->paginate(15);

        return OrderResource::collection($orders)
            ->additional(['meta' => [
                'totalSpent'   => $totalSpent,
                'totalTickets' => $totalTickets,
            ]]);
    }

    /**
     * Create a new order (supports guest checkout)
     *
     * This endpoint handles atomic stock validation and supports guest purchases.
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Commande créée avec succès.",
     *   "data": {
     *     "id": "01HXE2K3M4N5P6Q7R8S9T0V1W7",
     *     "userId": null,
     *     "firstName": "John",
     *     "lastName": "Doe",
     *     "email": "john@example.com",
     *     "phone": "+22890123456",
     *     "totalAmount": 100000,
     *     "status": "pending",
     *     "items": [
     *       {
     *         "ticketTypeId": "01HXE2K3M4N5P6Q7R8S9T0V1W6",
     *         "quantity": 2,
     *         "unitPrice": 50000,
     *         "subtotal": 100000
     *       }
     *     ]
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Stock insuffisant pour \"VIP\". Disponible: 5, Demandé: 10"
     * }
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            /** @var array{user_id?: string|null, first_name: string, last_name: string, email: string, phone: string, delivery_method: string, payment_method?: string|null, coupon_code?: string|null, items: array<int, array{ticket_type_id: string, quantity: int}>} $validated */
            $validated = $request->validated();

            $sanctumUser = $request->user('sanctum') ?? $request->user();
            if ($sanctumUser && empty($validated['user_id'])) {
                $validated['user_id'] = $sanctumUser->id;
            } elseif (empty($validated['user_id'])) {
                $existingUser = \App\Models\User::where('email', $validated['email'])->first();
                if ($existingUser) {
                    $validated['user_id'] = $existingUser->id;
                }
            }

            $order = $this->createOrderAction->execute($validated);

            return $this->created(
                data: new OrderResource($order),
                message: 'Commande créée avec succès.',
            );
        } catch (\DomainException $e) {
            return $this->error(
                message: $e->getMessage(),
                status: 422,
            );
        }
    }

    /**
     * Show a single order
     *
     * @response 200 {
     *   "success": true,
     *   "message": "",
     *   "data": {
     *     "id": "01HXE2K3M4N5P6Q7R8S9T0V1W7",
     *     "userId": null,
     *     "firstName": "John",
     *     "lastName": "Doe",
     *     "email": "john@example.com",
     *     "phone": "+22890123456",
     *     "totalAmount": 100000,
     *     "status": "pending",
     *     "items": [...],
     *     "tickets": [...]
     *   }
     * }
     */
    public function show(Order $order): JsonResponse
    {
        // Load relations and counts. L'organisateur accompagne l'événement :
        // la fiche de commande le nomme, et le chercher ligne par ligne aurait
        // coûté une requête par type de billet.
        $order->load([
            'items.ticketType.event.venue',
            'items.ticketType.event.organizer',
            'tickets.ticketType.event.venue',
            'payments',
            'downloadLink',
            'user',
        ]);
        $order->loadCount('items');
        $order->loadCount('tickets');

        return $this->success(
            data: new OrderResource($order),
        );
    }

    /**
     * Cancel an order
     *
     * Cancels the order and releases stock back to availability.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Commande annulée avec succès.",
     *   "data": {
     *     "id": "01HXE2K3M4N5P6Q7R8S9T0V1W7",
     *     "status": "cancelled"
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Cette commande est déjà annulée."
     * }
     */
    public function cancel(Order $order): JsonResponse
    {
        // Check authorization manually
        $user = auth()->user();
        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        // Check if user owns the order or is admin
        $isOwner = (string) $order->user_id === (string) $user->id
            || ($user->email !== null && strtolower((string) $order->email) === strtolower((string) $user->email));

        if (! $isOwner && ! $user->hasRole('admin')) {
            abort(404); // Return 404 instead of 403 for security
        }

        try {
            $order = $this->cancelOrderAction->execute(['order' => $order]);

            return $this->success(
                data: new OrderResource($order),
                message: 'Commande annulée avec succès.',
            );
        } catch (\DomainException $e) {
            return $this->error(
                message: $e->getMessage(),
                status: 422,
            );
        }
    }

    /**
     * Request refund for an order
     *
     * Cancels the reservation and refunds all valid tickets of the order if eligible.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Commande et billets remboursés avec succès.",
     *   "data": { ... }
     * }
     */
    public function refund(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        $isOwner = (string) $order->user_id === (string) $user->id
            || ($user->email !== null && strtolower((string) $order->email) === strtolower((string) $user->email));

        $isAdmin = $user->hasRole('admin');

        if (! $isOwner && ! $isAdmin) {
            abort(404);
        }

        if ($order->status === OrderStatus::REFUNDED) {
            return $this->error(
                message: 'Cette commande a déjà été remboursée.',
                status: 422,
            );
        }

        $validTickets = $order->tickets()->where('status', TicketStatus::VALID)->get();

        if ($validTickets->isEmpty()) {
            return $this->error(
                message: 'Aucun billet valide éligible au remboursement dans cette commande.',
                status: 422,
            );
        }

        $reason = $request->input('reason', 'Demande de remboursement client');
        $forceRefund = $isAdmin && $request->boolean('force_refund');

        try {
            foreach ($validTickets as $ticket) {
                $this->refundTicketAction->execute([
                    'ticket' => $ticket,
                    'reason' => $reason,
                    'force_refund' => $forceRefund,
                ]);
            }

            $order->refresh();
            $order->load(['tickets.ticketType.event', 'items']);

            return $this->success(
                data: new OrderResource($order),
                message: 'Commande et billets remboursés avec succès.',
            );
        } catch (\DomainException $e) {
            return $this->error(
                message: $e->getMessage(),
                status: 422,
            );
        }
    }
}
