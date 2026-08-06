<?php

declare(strict_types=1);

use App\Enums\WithdrawalStatus;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Organizer;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Withdrawal;
use App\Support\Commission;
use Database\Seeders\ScreenPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

/**
 * Donne des recettes à un organisateur : une commande payée sur un type de
 * billet de l'un de ses événements. Sans ça son solde disponible est nul, et
 * toute demande de retrait est refusée — ce qui est bien le but.
 */
function organizerWithRevenue(float $gross = 1_000_000): Organizer
{
    $organizer = Organizer::factory()->create();
    $event = Event::factory()->create(['organizer_id' => $organizer->id]);
    $ticketType = TicketType::factory()->create(['event_id' => $event->id]);
    $order = Order::factory()->paid()->create();

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'ticket_type_id' => $ticketType->id,
        'quantity' => 1,
        'unit_price' => $gross,
        'subtotal' => $gross,
    ]);

    return $organizer;
}

/**
 * Un compte d'administration.
 *
 * Le seeder est rejoué après création du rôle : comme en production, `admin` ne
 * tient pas l'accès de son nom mais de la permission `screen.withdrawals` que le
 * seeder lui accorde. Sans lui, la route refuse — et c'est le comportement
 * attendu, pas un défaut du test.
 */
function admin(): User
{
    $user = User::factory()->create();
    Role::findOrCreate('admin');
    $user->assignRole('admin');
    seedScreens();

    return $user;
}

function organizerManagerFor(Organizer $organizer): User
{
    $user = User::factory()->create();
    Role::findOrCreate('organizer-manager');
    $user->assignRole('organizer-manager');
    $organizer->update(['user_id' => $user->id]);
    seedScreens();

    return $user;
}

function seedScreens(): void
{
    app(ScreenPermissionSeeder::class)->run();
    app(PermissionRegistrar::class)->forgetCachedPermissions();
}

it('creates a withdrawal with a requester phone and a payment method', function (): void {
    $organizer = organizerWithRevenue();

    $payload = [
        'organizer_id' => $organizer->id,
        'requester_phone' => '+22890112233',
        'amount' => 250000,
        'payment_method' => 'flooz',
    ];

    $this->actingAs(admin())
        ->postJson('/api/v1/withdrawals', $payload)
        ->assertCreated()
        ->assertJsonPath('data.organizerId', $organizer->id)
        ->assertJsonPath('data.requesterPhone', '+22890112233')
        ->assertJsonPath('data.paymentMethod', 'flooz')
        ->assertJsonPath('data.paymentMethodLabel', 'Flooz')
        ->assertJsonPath('data.status', 'pending');

    $this->assertDatabaseHas('withdrawals', [
        'organizer_id' => $organizer->id,
        'requester_phone' => '+22890112233',
        'amount' => '250000.00',
        'payment_method' => 'flooz',
        'status' => 'pending',
    ]);
});

it('refuses a withdrawal above the organizer available balance', function (): void {
    $organizer = organizerWithRevenue(gross: 100_000);
    $available = Commission::netFor(100_000);

    $this->actingAs(admin())
        ->postJson('/api/v1/withdrawals', [
            'organizer_id' => $organizer->id,
            'requester_phone' => '+22890112233',
            'amount' => $available + 1,
            'payment_method' => 'flooz',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('amount');

    $this->assertDatabaseCount('withdrawals', 0);
});

it('counts pending requests against the balance so it cannot be spent twice', function (): void {
    $organizer = organizerWithRevenue(gross: 100_000);
    $available = Commission::netFor(100_000);

    Withdrawal::create([
        'organizer_id' => $organizer->id,
        'requester_phone' => '+22890112233',
        'amount' => $available,
        'payment_method' => 'flooz',
        'status' => WithdrawalStatus::PENDING,
    ]);

    $this->actingAs(admin())
        ->postJson('/api/v1/withdrawals', [
            'organizer_id' => $organizer->id,
            'requester_phone' => '+22890112233',
            'amount' => $available,
            'payment_method' => 'flooz',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('amount');

    expect(Withdrawal::count())->toBe(1);
});

it('denies the withdrawals screen to a user without a back-office role', function (): void {
    $organizer = organizerWithRevenue();

    $this->actingAs(User::factory()->create())
        ->postJson('/api/v1/withdrawals', [
            'organizer_id' => $organizer->id,
            'requester_phone' => '+22890112233',
            'amount' => 1000,
            'payment_method' => 'flooz',
        ])
        ->assertForbidden();
});

it('stops an organizer from requesting a withdrawal for someone else', function (): void {
    $own = organizerWithRevenue();
    $other = organizerWithRevenue();
    $manager = organizerManagerFor($own);

    $this->actingAs($manager)
        ->postJson('/api/v1/withdrawals', [
            'organizer_id' => $other->id,
            'requester_phone' => '+22890112233',
            'amount' => 1000,
            'payment_method' => 'flooz',
        ])
        ->assertForbidden();
});

it('only lists the requests of the calling organizer', function (): void {
    $own = organizerWithRevenue();
    $other = organizerWithRevenue();
    $manager = organizerManagerFor($own);

    Withdrawal::create([
        'organizer_id' => $own->id, 'requester_phone' => '+228901', 'amount' => 1000,
        'payment_method' => 'flooz', 'status' => WithdrawalStatus::PENDING,
    ]);
    Withdrawal::create([
        'organizer_id' => $other->id, 'requester_phone' => '+228902', 'amount' => 2000,
        'payment_method' => 'tmoney', 'status' => WithdrawalStatus::PENDING,
    ]);

    $this->actingAs($manager)
        ->getJson('/api/v1/withdrawals')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.organizerId', $own->id);
});

it('requires approval before a withdrawal can be marked paid', function (): void {
    $organizer = organizerWithRevenue();
    $withdrawal = Withdrawal::create([
        'organizer_id' => $organizer->id, 'requester_phone' => '+228901', 'amount' => 1000,
        'payment_method' => 'flooz', 'status' => WithdrawalStatus::PENDING,
    ]);

    $admin = admin();

    $this->actingAs($admin)
        ->postJson("/api/v1/withdrawals/{$withdrawal->id}/process", ['status' => 'paid'])
        ->assertStatus(422);

    $this->actingAs($admin)
        ->postJson("/api/v1/withdrawals/{$withdrawal->id}/process", [
            'status' => 'approved',
            'notes' => 'Vérifié par la compta',
        ])
        ->assertOk()
        ->assertJsonPath('data.status', 'approved')
        ->assertJsonPath('data.notes', 'Vérifié par la compta');

    // L'auteur et l'horodatage sont conservés.
    $withdrawal->refresh();
    expect($withdrawal->processed_by)->toBe($admin->id)
        ->and($withdrawal->processed_at)->not->toBeNull();

    $this->actingAs($admin)
        ->postJson("/api/v1/withdrawals/{$withdrawal->id}/process", [
            'status' => 'paid',
            'payout_reference' => 'FLZ-2026-000123',
        ])
        ->assertOk()
        ->assertJsonPath('data.status', 'paid')
        ->assertJsonPath('data.payoutReference', 'FLZ-2026-000123')
        ->assertJsonPath('data.isFinal', true);

    // Payé, le statut ne bouge plus.
    $this->actingAs($admin)
        ->postJson("/api/v1/withdrawals/{$withdrawal->id}/process", ['status' => 'rejected'])
        ->assertStatus(422);
});

it('refuses to mark a withdrawal paid without a transfer reference', function (): void {
    $organizer = organizerWithRevenue();
    $withdrawal = Withdrawal::create([
        'organizer_id' => $organizer->id, 'requester_phone' => '+228901', 'amount' => 1000,
        'payment_method' => 'flooz', 'status' => WithdrawalStatus::APPROVED,
    ]);

    $this->actingAs(admin())
        ->postJson("/api/v1/withdrawals/{$withdrawal->id}/process", ['status' => 'paid'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('payout_reference');

    expect($withdrawal->fresh()->status)->toBe(WithdrawalStatus::APPROVED);
});

it('keeps an organizer from processing their own withdrawal', function (): void {
    $organizer = organizerWithRevenue();
    $manager = organizerManagerFor($organizer);

    $withdrawal = Withdrawal::create([
        'organizer_id' => $organizer->id, 'requester_phone' => '+228901', 'amount' => 1000,
        'payment_method' => 'flooz', 'status' => WithdrawalStatus::PENDING,
    ]);

    $this->actingAs($manager)
        ->postJson("/api/v1/withdrawals/{$withdrawal->id}/process", ['status' => 'approved'])
        ->assertForbidden();
});
