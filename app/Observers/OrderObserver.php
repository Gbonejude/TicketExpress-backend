<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

final class OrderObserver
{
    public function created(Order $order): void
    {
        Log::info('Order created', ['order_id' => $order->id]);
        // Dispatch events, invalidate cache, etc.
    }

    public function updated(Order $order): void
    {
        Log::info('Order updated', ['order_id' => $order->id]);
    }

    public function deleted(Order $order): void
    {
        Log::info('Order deleted', ['order_id' => $order->id]);
    }
}
