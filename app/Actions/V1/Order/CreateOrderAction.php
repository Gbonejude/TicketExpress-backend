<?php

declare(strict_types=1);

namespace App\Actions\V1\Order;

use App\Actions\Contracts\Action;
use App\Enums\CouponType;
use App\Enums\OrderStatus;
use App\Events\Order\OrderCreatedEvent;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\TicketType;
use Illuminate\Support\Facades\DB;

final class CreateOrderAction implements Action
{
    /**
     * Create an order with atomic stock management.
     *
     * @param  array{
     *     user_id?: string|null,
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     *     phone: string,
     *     delivery_method: string,
     *     payment_method?: string|null,
     *     coupon_code?: string|null,
     *     items: array<int, array{
     *         ticket_type_id: string,
     *         quantity: int
     *     }>
     * }  $data
     */
    public function execute(array $data): Order
    {
        return DB::transaction(function () use ($data): Order {
            $items = $data['items'];
            $coupon = null;

            // Validate and apply coupon if provided
            if (! empty($data['coupon_code'])) {
                $coupon = Coupon::where('code', $data['coupon_code'])->first();

                if (! $coupon) {
                    throw new \DomainException('Le code promo n\'existe pas.');
                }

                if ($coupon->used_count >= $coupon->max_usage) {
                    throw new \DomainException('Ce code promo a atteint sa limite d\'utilisation.');
                }

                if (now()->lt($coupon->start_date) || now()->gt($coupon->end_date)) {
                    throw new \DomainException('Ce code promo n\'est pas valide pour cette période.');
                }
            }

            $totalAmount = 0;
            $orderItems = [];

            // Process each item with atomic stock validation
            foreach ($items as $item) {
                $ticketType = TicketType::query()
                    ->where('id', $item['ticket_type_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $ticketType) {
                    throw new \DomainException('Le type de ticket n\'existe pas.');
                }

                $available = $ticketType->quantity - $ticketType->sold_quantity;

                if ($available < $item['quantity']) {
                    throw new \DomainException(
                        sprintf(
                            'Stock insuffisant pour "%s". Disponible: %d, Demandé: %d',
                            $ticketType->name,
                            $available,
                            $item['quantity'],
                        ),
                    );
                }

                // Increment sold_quantity atomically
                $ticketType->increment('sold_quantity', $item['quantity']);

                $subtotal = $ticketType->price * $item['quantity'];
                $totalAmount += $subtotal;

                $orderItems[] = [
                    'ticket_type_id' => $ticketType->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $ticketType->price,
                    'subtotal' => $subtotal,
                ];
            }

            // Apply coupon discount if applicable
            if ($coupon) {
                /** @var CouponType $couponType */
                $couponType = $coupon->getAttribute('type');

                if ($couponType->value === 'percent') {
                    $discount = $totalAmount * ($coupon->value / 100);
                    $totalAmount -= $discount;
                } else {
                    // Fixed discount
                    $totalAmount -= $coupon->value;
                }

                // Ensure total is not negative
                $totalAmount = max($totalAmount, 0);

                // Increment coupon usage
                $coupon->increment('used_count');
            }

            // Create order
            $order = Order::create([
                'user_id' => $data['user_id'] ?? null,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'total_amount' => $totalAmount,
                'status' => OrderStatus::PENDING,
                'payment_method' => $data['payment_method'] ?? null,
                'delivery_method' => $data['delivery_method'],
            ]);

            // Create order items
            foreach ($orderItems as $orderItem) {
                $order->items()->create($orderItem);
            }

            $order->load('items.ticketType');

            // Dispatch OrderCreatedEvent AFTER transaction commit
            event(new OrderCreatedEvent($order));

            // Real-time (silent) refresh signals for the back-office lists.
            DB::afterCommit(function () use ($order): void {
                \App\Events\ResourceChangedEvent::dispatch('orders', 'created', $order->id);
                \App\Events\ResourceChangedEvent::dispatch('tickets', 'created', null);
                \App\Events\ResourceChangedEvent::dispatch('payments', 'created', null);
            });

            return $order;
        });
    }
}
