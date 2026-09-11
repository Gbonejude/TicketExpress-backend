<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->organizer = Organizer::factory()->create(['is_active' => true]);
});

function staffUser(): User
{
    $user = User::factory()->create();
    $user->assignRole(UserRole::ADMIN->value);

    return $user;
}

it('hides a draft event from the public catalogue', function (): void {
    Event::factory()->for($this->organizer)->draft()->create(['title' => 'Brouillon secret']);
    Event::factory()->for($this->organizer)->create(['title' => 'Concert publié']);

    $titles = collect($this->getJson('/api/v1/events')->assertOk()->json('data'))->pluck('title');

    expect($titles)->toContain('Concert publié')
        ->and($titles)->not->toContain('Brouillon secret');
});

it('hides a cancelled event from the public catalogue', function (): void {
    Event::factory()->for($this->organizer)->cancelled()->create(['title' => 'Annulé']);

    $titles = collect($this->getJson('/api/v1/events')->assertOk()->json('data'))->pluck('title');

    expect($titles)->not->toContain('Annulé');
});

it('does not serve a draft event by its own URL', function (): void {
    $draft = Event::factory()->for($this->organizer)->draft()->create();

    $this->getJson("/api/v1/events/{$draft->id}")->assertNotFound();
});

it('still shows the draft to the back-office', function (): void {
    $draft = Event::factory()->for($this->organizer)->draft()->create(['title' => 'Brouillon secret']);

    $this->actingAs(staffUser(), 'sanctum')
        ->getJson("/api/v1/events/{$draft->id}")
        ->assertOk()
        ->assertJsonPath('data.title', 'Brouillon secret');
});
