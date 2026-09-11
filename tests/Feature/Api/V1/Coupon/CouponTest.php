<?php

declare(strict_types=1);

use App\Models\Coupon;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ScreenPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(ScreenPermissionSeeder::class);
    $this->user = User::factory()->create();
    $this->user->givePermissionTo('screen.coupons');
});

it('can validate coupon publicly without authentication', function (): void {
    $coupon = Coupon::factory()->create([
        'code' => 'TEST20',
        'type' => 'percent',
        'value' => 20,
        'max_usage' => 100,
        'used_count' => 10,
        'start_date' => now(),
        'end_date' => now()->addMonth(),
    ]);

    $response = $this->getJson('/api/v1/coupons/validate?code=TEST20');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'valid' => true,
                'coupon' => [
                    'code' => 'TEST20',
                    'type' => 'percent',
                    'value' => '20.00',
                ],
            ],
        ]);
});

it('rejects invalid coupon code', function (): void {
    $response = $this->getJson('/api/v1/coupons/validate?code=INVALID');

    $response->assertStatus(422)
        ->assertJson(['success' => false]);

    expect($response->json('message'))->toContain('n\'existe pas');
});

it('rejects expired coupon', function (): void {
    Coupon::factory()->create([
        'code' => 'EXPIRED',
        'start_date' => now()->subMonths(2),
        'end_date' => now()->subMonth(),
    ]);

    $response = $this->getJson('/api/v1/coupons/validate?code=EXPIRED');

    $response->assertStatus(422)
        ->assertJson(['success' => false]);

    expect($response->json('message'))->toContain('pas valide');
});

it('rejects coupon that reached usage limit', function (): void {
    Coupon::factory()->create([
        'code' => 'LIMITED',
        'max_usage' => 10,
        'used_count' => 10,
        'start_date' => now(),
        'end_date' => now()->addMonth(),
    ]);

    $response = $this->getJson('/api/v1/coupons/validate?code=LIMITED');

    $response->assertStatus(422)
        ->assertJson(['success' => false]);

    expect($response->json('message'))->toContain('limite');
});

it('can create coupon with authentication', function (): void {
    // Un coupon vise désormais exactement un événement, qui borne sa validité.
    $event = Event::factory()->create(['end_date' => now()->addMonths(6)]);

    $payload = [
        'code' => 'NEWCODE',
        'type' => 'percent',
        'value' => 15,
        'max_usage' => 100,
        'start_date' => now()->toDateTimeString(),
        'end_date' => now()->addMonths(2)->toDateTimeString(),
        'event_ids' => [$event->id],
    ];

    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/coupons', $payload);

    $response->assertCreated()
        ->assertJson(['success' => true]);

    expect(Coupon::where('code', 'NEWCODE')->exists())->toBeTrue();
});

it('can update coupon', function (): void {
    $coupon = Coupon::factory()->create([
        'code' => 'OLDCODE',
        'value' => 10,
    ]);

    $payload = [
        'value' => 25,
    ];

    $response = $this->actingAs($this->user)
        ->putJson("/api/v1/coupons/{$coupon->id}", $payload);

    $response->assertOk()
        ->assertJson(['success' => true]);

    $coupon->refresh();
    expect($coupon->value)->toBe('25.00');
});

it('can delete coupon', function (): void {
    $coupon = Coupon::factory()->create(['code' => 'DELETEME']);

    $response = $this->actingAs($this->user)
        ->deleteJson("/api/v1/coupons/{$coupon->id}");

    $response->assertNoContent();

    expect(Coupon::find($coupon->id))->toBeNull();
});

it('validates coupon for specific event', function (): void {
    $event = Event::factory()->create();
    $coupon = Coupon::factory()->create([
        'code' => 'EVENTONLY',
        'start_date' => now(),
        'end_date' => now()->addMonth(),
    ]);

    $coupon->events()->attach($event->id);

    $response = $this->getJson("/api/v1/coupons/validate?code=EVENTONLY&event_id={$event->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'valid' => true,
            ],
        ]);
});
