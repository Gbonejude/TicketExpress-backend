<?php

declare(strict_types=1);

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\User;
use Database\Seeders\ScreenPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

/**
 * Un utilisateur avec un rôle donné et ses permissions d'écran semées.
 *
 * Nom distinct de `withRole()` (EventStatsScopeTest) : Pest charge tous les
 * fichiers de test dans le même processus, deux fonctions homonymes seraient une
 * redéclaration fatale.
 */
function scopedRoleUser(string $role): User
{
    $user = User::factory()->create();
    Role::findOrCreate($role);
    $user->assignRole($role);
    app(ScreenPermissionSeeder::class)->run();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    return $user;
}

/** @return array{0: User, 1: Organizer} */
function managerOwning(): array
{
    $manager = scopedRoleUser('organizer-manager');
    $organizer = Organizer::factory()->create(['user_id' => $manager->id, 'is_active' => true]);

    return [$manager, $organizer];
}

it('shows an organizer only their own events in the listing', function (): void {
    [$manager, $organizer] = managerOwning();

    $mine = Event::factory()->count(2)->create([
        'organizer_id' => $organizer->id,
        'status' => EventStatus::PUBLISHED,
    ]);

    Event::factory()->count(3)->create([
        'organizer_id' => Organizer::factory()->create(['is_active' => true])->id,
        'status' => EventStatus::PUBLISHED,
    ]);

    $response = $this->actingAs($manager)->getJson('/api/v1/events')->assertOk();

    expect($response->json('meta.total'))->toBe(2);

    $returnedIds = collect($response->json('data'))->pluck('id')->sort()->values()->all();
    expect($returnedIds)->toBe($mine->pluck('id')->sort()->values()->all());
});

it('shows an admin every organizer’s events', function (): void {
    $admin = scopedRoleUser('admin');

    Event::factory()->count(2)->create(['organizer_id' => Organizer::factory()->create()->id]);
    Event::factory()->count(3)->create(['organizer_id' => Organizer::factory()->create()->id]);

    $this->actingAs($admin)
        ->getJson('/api/v1/events')
        ->assertOk()
        ->assertJsonPath('meta.total', 5);
});

it('hides another organizer’s event behind a 404 on show', function (): void {
    [$manager] = managerOwning();

    $foreign = Event::factory()->create([
        'organizer_id' => Organizer::factory()->create(['is_active' => true])->id,
        'status' => EventStatus::PUBLISHED,
    ]);

    $this->actingAs($manager)
        ->getJson("/api/v1/events/{$foreign->id}")
        ->assertNotFound();
});

it('lets an organizer update their own event but not another’s', function (): void {
    [$manager, $organizer] = managerOwning();

    $mine = Event::factory()->create(['organizer_id' => $organizer->id]);
    $foreign = Event::factory()->create([
        'organizer_id' => Organizer::factory()->create()->id,
    ]);

    $this->actingAs($manager)
        ->putJson("/api/v1/events/{$mine->id}", ['title' => 'Titre mis à jour'])
        ->assertOk();

    $this->actingAs($manager)
        ->putJson("/api/v1/events/{$foreign->id}", ['title' => 'Tentative interdite'])
        ->assertForbidden();
});

it('refuses to let an organizer create an event for another organizer', function (): void {
    [$manager] = managerOwning();

    $otherOrganizer = Organizer::factory()->create();

    $this->actingAs($manager)
        ->postJson('/api/v1/events', [
            'organizer_id' => $otherOrganizer->id,
            'category_id' => \App\Models\EventCategory::factory()->create()->id,
            'title' => 'Événement usurpé',
            'slug' => 'evenement-usurpe',
            'description' => 'x',
            'start_date' => now()->addWeek()->toDateTimeString(),
            'end_date' => now()->addWeek()->addHours(3)->toDateTimeString(),
        ])
        ->assertForbidden();
});
