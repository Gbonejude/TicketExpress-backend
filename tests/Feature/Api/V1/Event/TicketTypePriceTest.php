<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Event;
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

it('refuses a ticket type priced at zero', function (): void {
    $this->actingAs($this->admin, 'sanctum')
        ->postJson("/api/v1/events/{$this->event->id}/ticket-types", [
            'name' => 'Gratuit',
            'price' => 0,
            'quantity' => 50,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('price');
});

it('accepts a ticket type with a positive price', function (): void {
    $this->actingAs($this->admin, 'sanctum')
        ->postJson("/api/v1/events/{$this->event->id}/ticket-types", [
            'name' => 'Standard',
            'price' => 5000,
            'quantity' => 50,
        ])
        ->assertCreated();
});
