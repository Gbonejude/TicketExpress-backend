<?php

declare(strict_types=1);

namespace App\Actions\V1\Order;

use App\Actions\Contracts\Action;
use App\Enums\OrderStatus;
use App\Events\Order\OrderCancelledEvent;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

final class CancelOrderAction implements Action
{
    /**
     * Cancel an order and release stock atomically.
     *
     * @param  array{order: Order}  $data
     */
    public function execute(array $data): Order
    {
        $order = $data['order'];

        if ($order->status === OrderStatus::CANCELLED) {
            throw new \DomainException('Cette commande est déjà annulée.');
        }

        if ($order->status === OrderStatus::PAID) {
            throw new \DomainException('Impossible d\'annuler une commande payée. Demandez un remboursement.');
        }

        return DB::transaction(function () use ($order): Order {
            // Load order items
            $orderItems = DB::table('order_items')
                ->where('order_id', $order->id)
                ->get();

            // Release stock for each order item atomically
            foreach ($orderItems as $item) {
                DB::table('ticket_types')
                    ->where('id', $item->ticket_type_id)
                    ->decrement('sold_quantity', $item->quantity);
            }

            // Update order status
            $order->update([
                'status' => OrderStatus::CANCELLED,
                'cancelled_at' => now(),
            ]);

            $order = $order->fresh() ?? $order;

            // Dispatch OrderCancelledEvent
            event(new OrderCancelledEvent($order));

            return $order;
        });
    }
}
