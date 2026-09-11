<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole(UserRole::SUPER_ADMIN->value);

    $this->event = Event::factory()->create([
        'start_date' => now()->addDays(10),
        'end_date' => now()->addDays(12),
    ]);
});

function baseTicketType(): array
{
    return ['name' => 'VIP', 'price' => 10000, 'quantity' => 100];
}

it('accepts a ticket type whose sale and promotion nest inside the event', function (): void {
    $this->actingAs($this->admin, 'sanctum')
        ->postJson("/api/v1/events/{$this->event->id}/ticket-types", [
            ...baseTicketType(),
            'sale_start_date' => now()->addDay()->toDateTimeString(),
            'sale_end_date' => now()->addDays(9)->toDateTimeString(),
            'promotional_price' => 7500,
            'promotion_start_date' => now()->addDays(2)->toDateTimeString(),
            'promotion_end_date' => now()->addDays(8)->toDateTimeString(),
        ])
        ->assertCreated();
});

it('refuses a sale that ends after the event', function (): void {
    $this->actingAs($this->admin, 'sanctum')
        ->postJson("/api/v1/events/{$this->event->id}/ticket-types", [
            ...baseTicketType(),
            'sale_start_date' => now()->addDay()->toDateTimeString(),
            'sale_end_date' => now()->addDays(13)->toDateTimeString(),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('sale_end_date');
});

it('refuses a promotion that ends after the sale window', function (): void {
    $this->actingAs($this->admin, 'sanctum')
        ->postJson("/api/v1/events/{$this->event->id}/ticket-types", [
            ...baseTicketType(),
            'sale_start_date' => now()->addDay()->toDateTimeString(),
            'sale_end_date' => now()->addDays(9)->toDateTimeString(),
            'promotional_price' => 7500,
            'promotion_start_date' => now()->addDays(2)->toDateTimeString(),
            'promotion_end_date' => now()->addDays(10)->toDateTimeString(),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('promotion_end_date');
});

it('refuses a promotion that ends after the sale, through the promotions endpoint', function (): void {
    $tier = TicketType::factory()->for($this->event, 'event')->create([
        'price' => 10000,
        'sale_start_date' => now()->addDay(),
        'sale_end_date' => now()->addDays(9),
    ]);

    $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/v1/promotions/{$tier->id}", [
            'promotional_price' => 7500,
            'promotion_start_date' => now()->addDays(2)->toDateTimeString(),
            'promotion_end_date' => now()->addDays(10)->toDateTimeString(),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('promotion_end_date');
});

it('accepts a promotion nested in the sale window through the promotions endpoint', function (): void {
    $tier = TicketType::factory()->for($this->event, 'event')->create([
        'price' => 10000,
        'sale_start_date' => now()->addDay(),
        'sale_end_date' => now()->addDays(9),
    ]);

    $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/v1/promotions/{$tier->id}", [
            'promotional_price' => 7500,
            'promotion_start_date' => now()->addDays(2)->toDateTimeString(),
            'promotion_end_date' => now()->addDays(8)->toDateTimeString(),
        ])
        ->assertOk();
});
