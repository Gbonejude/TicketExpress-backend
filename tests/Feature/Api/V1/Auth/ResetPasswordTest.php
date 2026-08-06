<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

/**
 * `forgot-password` was sending the mail all along; nothing consumed the token
 * it carried, so the link led nowhere. These cover the other half.
 */
beforeEach(function (): void {
    $this->user = User::factory()->create([
        'email' => 'jean.dupont@example.com',
        'password' => Hash::make('AncienMotDePasse1!'),
    ]);
});

it('resets the password with a valid token', function (): void {
    $token = Password::createToken($this->user);

    $this->postJson('/api/v1/auth/reset-password', [
        'email' => $this->user->email,
        'token' => $token,
        'password' => 'NouveauMotDePasse1!',
        'password_confirmation' => 'NouveauMotDePasse1!',
    ])->assertOk();

    expect(Hash::check('NouveauMotDePasse1!', $this->user->fresh()->password))->toBeTrue();
});

it('lets the user sign in with the new password afterwards', function (): void {
    $token = Password::createToken($this->user);

    $this->postJson('/api/v1/auth/reset-password', [
        'email' => $this->user->email,
        'token' => $token,
        'password' => 'NouveauMotDePasse1!',
        'password_confirmation' => 'NouveauMotDePasse1!',
    ])->assertOk();

    $this->postJson('/api/v1/auth/login', [
        'email' => $this->user->email,
        'password' => 'NouveauMotDePasse1!',
    ])->assertOk()->assertJsonPath('success', true);
});

it('revokes existing access tokens', function (): void {
    // A reset is how someone recovers a compromised account: leaving the
    // attacker's token alive would defeat the point.
    $this->user->createToken('web');
    expect($this->user->tokens()->count())->toBe(1);

    $token = Password::createToken($this->user);

    $this->postJson('/api/v1/auth/reset-password', [
        'email' => $this->user->email,
        'token' => $token,
        'password' => 'NouveauMotDePasse1!',
        'password_confirmation' => 'NouveauMotDePasse1!',
    ])->assertOk();

    expect($this->user->tokens()->count())->toBe(0);
});

it('refuses an invalid token', function (): void {
    $this->postJson('/api/v1/auth/reset-password', [
        'email' => $this->user->email,
        'token' => 'jeton-bidon',
        'password' => 'NouveauMotDePasse1!',
        'password_confirmation' => 'NouveauMotDePasse1!',
    ])->assertStatus(422);

    expect(Hash::check('AncienMotDePasse1!', $this->user->fresh()->password))->toBeTrue();
});

it('refuses a token that has already been used', function (): void {
    $token = Password::createToken($this->user);

    $payload = [
        'email' => $this->user->email,
        'token' => $token,
        'password' => 'NouveauMotDePasse1!',
        'password_confirmation' => 'NouveauMotDePasse1!',
    ];

    $this->postJson('/api/v1/auth/reset-password', $payload)->assertOk();
    $this->postJson('/api/v1/auth/reset-password', $payload)->assertStatus(422);
});

it('requires the confirmation to match', function (): void {
    $this->postJson('/api/v1/auth/reset-password', [
        'email' => $this->user->email,
        'token' => Password::createToken($this->user),
        'password' => 'NouveauMotDePasse1!',
        'password_confirmation' => 'AutreChose1!',
    ])->assertStatus(422)->assertJsonValidationErrors(['password']);
});
