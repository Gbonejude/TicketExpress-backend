<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\EventOccurrence;
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

it('accepts an occurrence that fits inside the event window', function (): void {
    $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/v1/event-occurrences', [
            'event_id' => $this->event->id,
            'start_date' => now()->addDays(10)->addHours(2)->toDateTimeString(),
            'end_date' => now()->addDays(10)->addHours(5)->toDateTimeString(),
        ])
        ->assertCreated();
});

it('refuses an occurrence that starts before the event', function (): void {
    $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/v1/event-occurrences', [
            'event_id' => $this->event->id,
            'start_date' => now()->addDays(9)->toDateTimeString(),
            'end_date' => now()->addDays(9)->addHours(3)->toDateTimeString(),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('start_date');
});

it('refuses an occurrence that ends after the event', function (): void {
    $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/v1/event-occurrences', [
            'event_id' => $this->event->id,
            'start_date' => now()->addDays(11)->toDateTimeString(),
            'end_date' => now()->addDays(13)->toDateTimeString(),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('end_date');
});

it('refuses to move an occurrence past the event window on update', function (): void {
    $occurrence = EventOccurrence::factory()->for($this->event)->create([
        'start_date' => now()->addDays(10)->addHours(2),
        'end_date' => now()->addDays(10)->addHours(5),
    ]);

    $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/v1/event-occurrences/{$occurrence->id}", [
            'end_date' => now()->addDays(13)->toDateTimeString(),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('end_date');
});
