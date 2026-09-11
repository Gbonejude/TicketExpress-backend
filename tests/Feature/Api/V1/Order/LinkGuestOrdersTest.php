<?php

declare(strict_types=1);

use App\Actions\V1\Order\LinkGuestOrdersToUserAction;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function linkOrders(User $user): int
{
    return app(LinkGuestOrdersToUserAction::class)->execute(['user' => $user]);
}

it('links a guest order by email', function (): void {
    $user = User::factory()->create(['email' => 'buyer@gmail.com', 'phone' => '+22890000001']);
    $order = Order::factory()->create(['user_id' => null, 'email' => 'buyer@gmail.com', 'phone' => '+22899999999']);

    expect(linkOrders($user))->toBe(1)
        ->and($order->fresh()->user_id)->toBe($user->id);
});

it('links a guest order by phone even when the email differs', function (): void {
    $user = User::factory()->create(['email' => 'me@gmail.com', 'phone' => '+22890000002']);
    $order = Order::factory()->create(['user_id' => null, 'email' => 'guest@gmail.com', 'phone' => '+22890000002']);

    linkOrders($user);

    expect($order->fresh()->user_id)->toBe($user->id);
});

it('matches the email regardless of case', function (): void {
    $user = User::factory()->create(['email' => 'Case@Gmail.com', 'phone' => '+22890000010']);
    // Téléphone volontairement différent : le rattachement se fait par l'e-mail.
    $order = Order::factory()->create(['user_id' => null, 'email' => 'case@gmail.com', 'phone' => '+22800000000']);

    linkOrders($user);

    expect($order->fresh()->user_id)->toBe($user->id);
});

it('leaves an unrelated guest order alone', function (): void {
    $user = User::factory()->create(['email' => 'me@gmail.com', 'phone' => '+22890000003']);
    $order = Order::factory()->create(['user_id' => null, 'email' => 'other@gmail.com', 'phone' => '+22811111111']);

    linkOrders($user);

    expect($order->fresh()->user_id)->toBeNull();
});

it('never steals an order that already belongs to someone', function (): void {
    $owner = User::factory()->create();
    $newcomer = User::factory()->create(['email' => 'shared@gmail.com', 'phone' => '+22890000004']);
    $order = Order::factory()->create(['user_id' => $owner->id, 'email' => 'shared@gmail.com']);

    linkOrders($newcomer);

    expect($order->fresh()->user_id)->toBe($owner->id);
});

it('claims matching guest orders when the buyer logs in', function (): void {
    $user = User::factory()->create([
        'email' => 'login@gmail.com',
        'password' => Hash::make('Password123!'),
    ]);
    $order = Order::factory()->create(['user_id' => null, 'email' => 'login@gmail.com']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'login@gmail.com',
        'password' => 'Password123!',
    ])->assertOk();

    expect($order->fresh()->user_id)->toBe($user->id);
});
