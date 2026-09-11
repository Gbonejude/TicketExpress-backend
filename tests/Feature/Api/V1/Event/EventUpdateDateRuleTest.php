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
    $this->admin->assignRole(UserRole::ADMIN->value);
});

it('refuses to move an event start date into the past', function (): void {
    $event = Event::factory()->create([
        'start_date' => now()->addWeek(),
        'end_date' => now()->addWeek()->addHours(3),
    ]);

    $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/v1/events/{$event->id}", [
            'start_date' => now()->subWeek()->toDateTimeString(),
            'end_date' => now()->subWeek()->addHours(3)->toDateTimeString(),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('start_date');
});

it('lets you move the start date to another future moment', function (): void {
    $event = Event::factory()->create([
        'start_date' => now()->addWeek(),
        'end_date' => now()->addWeek()->addHours(3),
    ]);

    $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/v1/events/{$event->id}", [
            'start_date' => now()->addMonth()->toDateTimeString(),
            'end_date' => now()->addMonth()->addHours(3)->toDateTimeString(),
        ])
        ->assertOk();
});

it('still lets you edit an event that has already started, its past start left as is', function (): void {
    // La date de début inchangée est tolérée : on corrige le titre d'un
    // événement en cours sans devoir le reprogrammer.
    $event = Event::factory()->ongoing()->create();

    $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/v1/events/{$event->id}", [
            'title' => 'Titre corrigé',
            'start_date' => $event->start_date->format('Y-m-d\TH:i'),
        ])
        ->assertOk();

    expect($event->fresh()->title)->toBe('Titre corrigé');
});
