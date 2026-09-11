<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole(UserRole::SUPER_ADMIN->value);

    // Fenêtre large : les dates de coupon des tests tiennent dedans, sauf quand
    // on les pousse volontairement hors bornes.
    $this->event = Event::factory()->create([
        'start_date' => now()->addDay(),
        'end_date' => now()->addMonths(6),
    ]);
});

function couponPayload(array $over = []): array
{
    return array_merge([
        'code' => 'CODE'.fake()->unique()->numberBetween(1000, 9999),
        'type' => 'percent',
        'value' => 15,
        'max_usage' => 100,
        'start_date' => now()->toDateTimeString(),
        'end_date' => now()->addMonth()->toDateTimeString(),
        'event_ids' => [test()->event->id],
    ], $over);
}

it('accepts a coupon valid in the future within its event', function (): void {
    $this->actingAs($this->admin)
        ->postJson('/api/v1/coupons', couponPayload())
        ->assertCreated();
});

it('requires exactly one event', function (): void {
    $this->actingAs($this->admin)
        ->postJson('/api/v1/coupons', couponPayload(['event_ids' => []]))
        ->assertStatus(422)
        ->assertJsonValidationErrors('event_ids');
});

it('refuses a coupon starting in the past', function (): void {
    $this->actingAs($this->admin)
        ->postJson('/api/v1/coupons', couponPayload([
            'start_date' => now()->subMonth()->toDateTimeString(),
        ]))
        ->assertStatus(422)
        ->assertJsonValidationErrors('start_date');
});

it('refuses a coupon ending before it starts', function (): void {
    $this->actingAs($this->admin)
        ->postJson('/api/v1/coupons', couponPayload([
            'end_date' => now()->subDay()->toDateTimeString(),
        ]))
        ->assertStatus(422)
        ->assertJsonValidationErrors('end_date');
});

it('refuses a coupon that stays valid after its event ends', function (): void {
    $this->actingAs($this->admin)
        ->postJson('/api/v1/coupons', couponPayload([
            'end_date' => now()->addMonths(8)->toDateTimeString(),
        ]))
        ->assertStatus(422)
        ->assertJsonValidationErrors('end_date');
});

it('refuses moving a coupon end date into the past on update', function (): void {
    $coupon = Coupon::factory()->create([
        'start_date' => now(),
        'end_date' => now()->addMonth(),
    ]);

    $this->actingAs($this->admin)
        ->putJson("/api/v1/coupons/{$coupon->id}", [
            'end_date' => now()->subMonth()->toDateTimeString(),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('end_date');
});

it('lets you edit a coupon without touching its dates', function (): void {
    $coupon = Coupon::factory()->create([
        'code' => 'OLDONE',
        'value' => 10,
        'start_date' => now()->subMonths(2),
        'end_date' => now()->subMonth(),
    ]);

    $this->actingAs($this->admin)
        ->putJson("/api/v1/coupons/{$coupon->id}", ['value' => 30])
        ->assertOk();
});
