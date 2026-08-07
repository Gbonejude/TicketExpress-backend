<?php

declare(strict_types=1);

use App\Models\Organizer;
use App\Models\User;
use App\Support\AbilityRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

/**
 * Un organisateur et son compte.
 *
 * @return array{0: User, 1: Organizer}
 */
function organizerAccount(): array
{
    $user = User::factory()->create();
    Role::findOrCreate('organizer-manager');
    $user->assignRole('organizer-manager');

    $organizer = Organizer::factory()->create(['user_id' => $user->id]);

    return [$user, $organizer->fresh()];
}

it('lets an organizer read their own profile', function (): void {
    [$user, $organizer] = organizerAccount();
    $organizer->update(['checkin_open_hours_before' => 6]);

    $this->actingAs($user)
        ->getJson('/api/v1/organizers/me')
        ->assertOk()
        ->assertJsonPath('data.id', $organizer->id)
        ->assertJsonPath('data.checkinOpenHoursBefore', 6);
});

it('lets an organizer set their own check-in window', function (): void {
    // Le geste que l'écran « Contrôle d'accès » doit permettre : poser une fois
    // ses habitudes, sans passer par l'administration.
    [$user, $organizer] = organizerAccount();

    $this->actingAs($user)
        ->putJson('/api/v1/organizers/me', [
            'checkin_open_hours_before' => 6,
            'checkin_close_hours_after' => 2,
        ])
        ->assertOk()
        ->assertJsonPath('data.checkinOpenHoursBefore', 6)
        ->assertJsonPath('data.checkinCloseHoursAfter', 2);

    expect($organizer->fresh()->checkin_open_hours_before)->toBe(6.0);
});

it('lets an organizer go back to the default by clearing the fields', function (): void {
    // Sans ça, une marge posée une fois ne pourrait plus jamais être retirée.
    [$user, $organizer] = organizerAccount();
    $organizer->update(['checkin_open_hours_before' => 6, 'checkin_close_hours_after' => 2]);

    $this->actingAs($user)
        ->putJson('/api/v1/organizers/me', [
            'checkin_open_hours_before' => null,
            'checkin_close_hours_after' => null,
        ])
        ->assertOk();

    expect($organizer->fresh()->checkin_open_hours_before)->toBeNull()
        ->and($organizer->fresh()->checkin_close_hours_after)->toBeNull();
});

it('refuses to let an organizer touch anything else through this endpoint', function (): void {
    // Le point de sécurité : `status` passe par l'administration. Exposé ici,
    // un organisateur s'approuverait lui-même.
    [$user, $organizer] = organizerAccount();
    $before = $organizer->status;

    $this->actingAs($user)
        ->putJson('/api/v1/organizers/me', [
            'checkin_open_hours_before' => 6,
            'checkin_close_hours_after' => 2,
            'status' => 'approved',
            'company_name' => 'Renommé en douce',
        ])
        ->assertOk();

    expect($organizer->fresh()->status)->toBe($before)
        ->and($organizer->fresh()->company_name)->toBe($organizer->company_name);
});

it('rejects a margin beyond a week', function (): void {
    [$user] = organizerAccount();

    $this->actingAs($user)
        ->putJson('/api/v1/organizers/me', [
            'checkin_open_hours_before' => 200,
            'checkin_close_hours_after' => 4,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('checkin_open_hours_before');
});

it('answers 404 for an account with no organizer profile', function (): void {
    $this->actingAs(User::factory()->create())
        ->getJson('/api/v1/organizers/me')
        ->assertNotFound();
});

it('keeps the endpoint closed to anonymous callers', function (): void {
    $this->getJson('/api/v1/organizers/me')->assertUnauthorized();
});

/**
 * L'entrée de menu « Contrôle d'accès » est-elle accordée à ce compte ?
 *
 * CASL retient la dernière règle applicable : une règle inversée posée après
 * `manage all` retire le droit. On reproduit ici cette lecture plutôt que de
 * chercher la simple présence du sujet.
 */
function grantsOrganizerProfile(User $user): bool
{
    $granted = false;

    foreach (AbilityRules::for($user) as $rule) {
        if ($rule['subject'] === 'all' && ($rule['inverted'] ?? false) === false) {
            $granted = true;
        }

        if ($rule['subject'] === 'organizer-profile') {
            $granted = ($rule['inverted'] ?? false) === false;
        }
    }

    return $granted;
}

it('grants the organizer-profile ability only to accounts that have one', function (): void {
    [$user] = organizerAccount();

    expect(grantsOrganizerProfile($user))->toBeTrue()
        ->and(grantsOrganizerProfile(User::factory()->create()))->toBeFalse();
});

it('keeps the check-in screen away from a super-admin who organizes nothing', function (): void {
    // `manage all` le lui donnerait par ricochet : il ouvrirait un formulaire
    // sans objet, que l'API refuserait ensuite par un 404.
    $admin = User::factory()->create();
    Role::findOrCreate('super-admin');
    $admin->assignRole('super-admin');

    expect(grantsOrganizerProfile($admin))->toBeFalse();

    $this->actingAs($admin)
        ->getJson('/api/v1/organizers/me')
        ->assertNotFound();
});

it('gives it back to a super-admin who is also an organizer', function (): void {
    $admin = User::factory()->create();
    Role::findOrCreate('super-admin');
    $admin->assignRole('super-admin');
    Organizer::factory()->create(['user_id' => $admin->id]);

    expect(grantsOrganizerProfile($admin->fresh()))->toBeTrue();
});
