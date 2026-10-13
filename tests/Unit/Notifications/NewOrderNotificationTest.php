<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\User;
use App\Notifications\NewOrderNotification;

it('stores the order reference, not the whole order with the buyer details', function (): void {
    $order = Order::factory()->create(['email' => 'acheteur@example.tg']);
    $user = User::factory()->create();

    $data = (new NewOrderNotification('Nouvelle commande', 'view_order', '/orders', $order))->toArray($user);

    expect($data)->toHaveKeys(['order_id', 'order_number'])
        ->not->toHaveKey('order')
        ->and(json_encode($data))->not->toContain('acheteur@example.tg');
});
