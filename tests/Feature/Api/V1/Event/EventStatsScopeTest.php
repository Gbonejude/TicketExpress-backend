<?php

declare(strict_types=1);

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Database\Seeders\ScreenPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

function withRole(string $role): User
{
    $user = User::factory()->create();
    Role::findOrCreate($role);
    $user->assignRole($role);
    app(ScreenPermissionSeeder::class)->run();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return $user;
}

it('lets an organizer read the box office of their own event', function (): void {
    $manager = withRole('organizer-manager');
    $organizer = Organizer::factory()->create(['user_id' => $manager->id]);
    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
        'status' => EventStatus::PUBLISHED,
    ]);

    $this->actingAs($manager)
        ->getJson("/api/v1/events/{$event->id}/stats")
        ->assertOk()
        ->assertJsonPath('data.event.id', $event->id);
});

it('stops an organizer from reading the box office of another organizer', function (): void {
    $manager = withRole('organizer-manager');
    Organizer::factory()->create(['user_id' => $manager->id]);

    $foreign = Event::factory()->create([
        'organizer_id' => Organizer::factory()->create()->id,
        'status' => EventStatus::PUBLISHED,
    ]);

    $this->actingAs($manager)
        ->getJson("/api/v1/events/{$foreign->id}/stats")
        ->assertForbidden();
});

it('lets an admin read any box office', function (): void {
    $admin = withRole('admin');
    $event = Event::factory()->create(['status' => EventStatus::PUBLISHED]);

    $this->actingAs($admin)
        ->getJson("/api/v1/events/{$event->id}/stats")
        ->assertOk();
});

it('recognises the owning organizer through the ticket policy', function (): void {
    // Le propriétaire est comparé sur `organizers.user_id`, pas sur
    // `events.organizer_id` : cette branche ne pouvait jamais être vraie avant.
    $manager = User::factory()->create();
    Role::findOrCreate('organizer-manager');
    $manager->assignRole('organizer-manager');

    $organizer = Organizer::factory()->create(['user_id' => $manager->id]);
    $event = Event::factory()->create(['organizer_id' => $organizer->id]);
    $ticketType = TicketType::factory()->create(['event_id' => $event->id]);
    $ticket = Ticket::factory()->create(['ticket_type_id' => $ticketType->id]);

    // Sans permission d'écran : seule la branche propriétaire peut autoriser.
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    expect($manager->can('update', $ticket))->toBeTrue();

    $stranger = User::factory()->create();
    $stranger->assignRole('organizer-manager');
    Organizer::factory()->create(['user_id' => $stranger->id]);

    expect($stranger->can('update', $ticket))->toBeFalse();
});
