<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('changes the password when the current one is given', function (): void {
    $user = User::factory()->create(['password' => Hash::make('Password123!')]);

    $this->actingAs($user)
        ->putJson('/api/v1/me/password', [
            'current_password' => 'Password123!',
            'password' => 'NouveauMot1!',
            'password_confirmation' => 'NouveauMot1!',
        ])
        ->assertOk();

    expect(Hash::check('NouveauMot1!', $user->fresh()->password))->toBeTrue();
});

it('refuses a wrong current password', function (): void {
    // Sans cette vérification, un poste laissé ouvert suffirait à verrouiller le
    // compte de son propriétaire.
    $user = User::factory()->create(['password' => Hash::make('Password123!')]);

    $this->actingAs($user)
        ->putJson('/api/v1/me/password', [
            'current_password' => 'PasLeBon1!',
            'password' => 'NouveauMot1!',
            'password_confirmation' => 'NouveauMot1!',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('current_password');

    expect(Hash::check('Password123!', $user->fresh()->password))->toBeTrue();
});

it('refuses a confirmation that does not match', function (): void {
    $user = User::factory()->create(['password' => Hash::make('Password123!')]);

    $this->actingAs($user)
        ->putJson('/api/v1/me/password', [
            'current_password' => 'Password123!',
            'password' => 'NouveauMot1!',
            'password_confirmation' => 'AutreChose1!',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('password');
});

it('refuses a weak password', function (): void {
    $user = User::factory()->create(['password' => Hash::make('Password123!')]);

    $this->actingAs($user)
        ->putJson('/api/v1/me/password', [
            'current_password' => 'Password123!',
            'password' => 'motdepasse',
            'password_confirmation' => 'motdepasse',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('password');
});

it('refuses an anonymous caller', function (): void {
    $this->putJson('/api/v1/me/password', [
        'current_password' => 'Password123!',
        'password' => 'NouveauMot1!',
        'password_confirmation' => 'NouveauMot1!',
    ])->assertUnauthorized();
});
