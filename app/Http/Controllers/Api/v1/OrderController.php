<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\V1\Order\CancelOrderAction;
use App\Actions\V1\Order\CreateOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Order\StoreOrderRequest;
use App\Http\Resources\V1\OrderResource;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
    ) {}

    /**
     * List orders
     *
     * For authenticated users, returns their orders.
     * For admins, returns all orders.
     *
     * @queryParam status Filter by status (pending, paid, cancelled, refunded). Example: paid
     * @queryParam email Filter by buyer email. Example: john@example.com
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
            ->with(['items.ticketType', 'tickets', 'downloadLink'])
            ->withCount(['items', 'tickets'])
            ->latest();

        // Back-office (admin / super-admin) sees every order; anyone else only
        // their own.
        $user = $request->user();
        $isAdmin = $user && $user->hasAnyRole(['admin', 'super-admin']);

        if (! $isAdmin) {
            $query->where('user_id', $user?->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('email')) {
            $query->where('email', $request->input('email'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->input('search');

            $query->where(function (Builder $q) use ($search): void {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(15);

        return OrderResource::collection($orders);
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
        // Load relations and counts
        $order->load(['items.ticketType', 'tickets', 'payments', 'downloadLink']);
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
        if ((string) $order->user_id !== (string) $user->id && ! $user->hasRole('admin')) {
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
}
