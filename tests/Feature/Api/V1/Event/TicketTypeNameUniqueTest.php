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
    $this->event = Event::factory()->create();
});

it('refuses a second ticket type with the same name on one event', function (): void {
    TicketType::factory()->for($this->event, 'event')->create(['name' => 'VIP']);

    $this->actingAs($this->admin, 'sanctum')
        ->postJson("/api/v1/events/{$this->event->id}/ticket-types", [
            'name' => 'VIP',
            'price' => 10000,
            'quantity' => 50,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

it('allows the same name on a different event', function (): void {
    TicketType::factory()->for($this->event, 'event')->create(['name' => 'VIP']);
    $other = Event::factory()->create();

    $this->actingAs($this->admin, 'sanctum')
        ->postJson("/api/v1/events/{$other->id}/ticket-types", [
            'name' => 'VIP',
            'price' => 10000,
            'quantity' => 50,
        ])
        ->assertCreated();
});

it('refuses renaming a ticket type onto another one of the same event', function (): void {
    TicketType::factory()->for($this->event, 'event')->create(['name' => 'VIP']);
    $standard = TicketType::factory()->for($this->event, 'event')->create(['name' => 'Standard']);

    $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/v1/events/{$this->event->id}/ticket-types/{$standard->id}", [
            'name' => 'VIP',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

it('lets a ticket type keep its own name on update', function (): void {
    $vip = TicketType::factory()->for($this->event, 'event')->create(['name' => 'VIP']);

    $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/v1/events/{$this->event->id}/ticket-types/{$vip->id}", [
            'name' => 'VIP',
        ])
        ->assertOk();
});
