<?php

declare(strict_types=1);

use App\Models\Organizer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('lets an account update its own name, e-mail and phone', function (): void {
    $user = User::factory()->create([
        'first_name' => 'Komi',
        'last_name' => 'CREPPY',
        'email' => 'komi@example.com',
        'phone' => '90112233',
    ]);

    $this->actingAs($user)
        ->putJson('/api/v1/me', [
            'first_name' => 'Kodjo',
            'last_name' => 'MENSAH',
            'email' => 'kodjo@example.com',
            'phone' => '90445566',
        ])
        ->assertOk()
        ->assertJsonPath('data.firstName', 'Kodjo')
        ->assertJsonPath('data.email', 'kodjo@example.com');

    expect($user->fresh()->last_name)->toBe('MENSAH')
        ->and($user->fresh()->phone)->toBe('90445566');
});

it('never lets an account grant itself a role', function (): void {
    // Le champ n'existe pas ici, contrairement à `users/{id}` : c'est ce qui
    // sépare « je corrige mon numéro » de « je me nomme super-admin ».
    Role::findOrCreate('participant');
    Role::findOrCreate('super-admin');

    $user = User::factory()->create();
    $user->assignRole('participant');

    $this->actingAs($user)
        ->putJson('/api/v1/me', [
            'first_name' => 'Komi',
            'last_name' => 'CREPPY',
            'email' => 'komi@example.com',
            'role' => 'super-admin',
        ])
        ->assertOk();

    expect($user->fresh()->hasRole('super-admin'))->toBeFalse()
        ->and($user->fresh()->hasRole('participant'))->toBeTrue();
});

it('keeps the organizer relation in the response', function (): void {
    // Le back-office range cette réponse dans la session : sans `organizer`, un
    // organisateur qui enregistre son profil se retrouverait traité comme un
    // administrateur par les écrans qui se bornent au sien.
    Role::findOrCreate('organizer-manager');

    $user = User::factory()->create();
    $user->assignRole('organizer-manager');

    $organizer = Organizer::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->putJson('/api/v1/me', [
            'first_name' => 'Komi',
            'last_name' => 'CREPPY',
            'email' => 'komi@example.com',
        ])
        ->assertOk()
        ->assertJsonPath('data.organizer.id', $organizer->id);
});

it('rejects an e-mail already taken by someone else', function (): void {
    User::factory()->create(['email' => 'occupe@example.com']);
    $user = User::factory()->create(['email' => 'komi@example.com']);

    $this->actingAs($user)
        ->putJson('/api/v1/me', [
            'first_name' => 'Komi',
            'last_name' => 'CREPPY',
            'email' => 'occupe@example.com',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

it('accepts the account keeping its own e-mail', function (): void {
    // La règle d'unicité doit s'ignorer elle-même, sinon on ne peut plus
    // corriger son nom sans changer d'adresse.
    $user = User::factory()->create(['email' => 'komi@example.com']);

    $this->actingAs($user)
        ->putJson('/api/v1/me', [
            'first_name' => 'Komi',
            'last_name' => 'CREPPY',
            'email' => 'komi@example.com',
        ])
        ->assertOk();
});

it('refuses an anonymous caller', function (): void {
    $this->putJson('/api/v1/me', [
        'first_name' => 'Komi',
        'last_name' => 'CREPPY',
        'email' => 'komi@example.com',
    ])->assertUnauthorized();
});
