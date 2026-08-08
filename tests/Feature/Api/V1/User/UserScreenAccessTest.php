<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

/** Un compte ordinaire : connecté, sans aucun droit d'administration. */
function plainAccount(string $role = 'participant'): User
{
    Role::findOrCreate($role);

    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('refuses to let an ordinary account promote itself', function (): void {
    // La faille que ce garde ferme : `UpdateUserAction` applique `role`, et le
    // groupe n'exigeait que d'être connecté. Un participant pouvait donc
    // s'attribuer super-admin sur son propre compte, d'un seul appel.
    Role::findOrCreate('super-admin');

    $user = plainAccount();

    $this->actingAs($user)
        ->putJson("/api/v1/users/{$user->id}", ['role' => 'super-admin'])
        ->assertForbidden();

    expect($user->fresh()->hasRole('super-admin'))->toBeFalse();
});

it('refuses to let an organizer read the user list', function (): void {
    // Une liste d'utilisateurs est un fichier de personnes : nom, e-mail,
    // téléphone. Elle ne s'ouvre pas à qui vend des billets.
    $user = plainAccount('organizer-manager');

    $this->actingAs($user)
        ->getJson('/api/v1/users')
        ->assertForbidden();
});

it('refuses to let an ordinary account create or delete a user', function (): void {
    $user = plainAccount();
    $target = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/v1/users', [
            'first_name' => 'Komi',
            'last_name' => 'CREPPY',
            'email' => 'komi@example.com',
            'phone' => '90112233',
        ])
        ->assertForbidden();

    $this->actingAs($user)
        ->deleteJson("/api/v1/users/{$target->id}")
        ->assertForbidden();

    expect(User::query()->whereKey($target->id)->exists())->toBeTrue();
});

it('still lets the users screen do its work', function (): void {
    // Le garde ne doit pas fermer l'écran qu'il protège : un rôle porteur de
    // `screen.users`, sans être super-admin, continue d'administrer les comptes.
    $role = Role::findOrCreate('admin');
    $role->givePermissionTo(Permission::findOrCreate('screen.users'));

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $target = User::factory()->create(['first_name' => 'Ancien']);

    $this->actingAs($admin)
        ->putJson("/api/v1/users/{$target->id}", ['first_name' => 'Nouveau'])
        ->assertOk();

    expect($target->fresh()->first_name)->toBe('Nouveau');
});

it('lets the organizers screen read the list, but not touch it', function (): void {
    // Le select « Responsable » de l'écran des organisateurs se remplit d'ici.
    // La lecture lui est donc ouverte ; le reste, non.
    $role = Role::findOrCreate('validateur');
    $role->givePermissionTo(Permission::findOrCreate('screen.organizers'));

    $user = User::factory()->create();
    $user->assignRole('validateur');

    $target = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/api/v1/users')
        ->assertOk();

    $this->actingAs($user)
        ->putJson("/api/v1/users/{$target->id}", ['first_name' => 'Nouveau'])
        ->assertForbidden();
});

it('still lets a super-admin through', function (): void {
    $user = User::factory()->create();
    $user->assignRole(UserRole::SUPER_ADMIN->value);

    $this->actingAs($user)
        ->getJson('/api/v1/users')
        ->assertOk();
});
