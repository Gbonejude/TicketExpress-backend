<?php

declare(strict_types=1);

use App\Models\User;

$payload = fn () => [
    'first_name' => 'Jean',
    'last_name' => 'Dupont',
    'email' => 'jean.dupont@example.com',
    'phone' => '+22890200001',
    'password' => 'Password123!',
    'password_confirmation' => 'Password123!',
];

it('creates a client account with password and returns user data', function () use ($payload) {
    $this->postJson('/api/v1/auth/register/client', $payload())
        ->assertCreated()
        ->assertJson(['success' => true, 'message' => 'Compte client créé avec succès.'])
        ->assertJsonStructure(['data' => ['token', 'user' => ['id', 'firstName', 'lastName', 'fullName', 'email', 'phone']]]);

    expect(User::where('email', 'jean.dupont@example.com')->exists())->toBeTrue();
    expect(User::where('phone', '+22890200001')->exists())->toBeTrue();
});

it('returns an access token so the new client is signed in immediately', function () use ($payload) {
    $token = $this->postJson('/api/v1/auth/register/client', $payload())
        ->assertCreated()
        ->json('data.token');

    expect($token)->toBeString()->not->toBeEmpty();

    // The token must work straight away — no second login round-trip.
    $this->withToken($token)
        ->getJson('/api/v1/me')
        ->assertSuccessful();
});

it('names the registration token after the X-Device-Name header', function () use ($payload) {
    $this->withHeader('X-Device-Name', 'android-chrome')
        ->postJson('/api/v1/auth/register/client', $payload())
        ->assertCreated();

    $user = User::where('email', 'jean.dupont@example.com')->first();

    expect($user->tokens()->first()->name)->toBe('android-chrome');
});

it('does not issue a token when registration fails validation', function () use ($payload) {
    $data = $payload();
    $data['email'] = 'invalid-email';

    $this->postJson('/api/v1/auth/register/client', $data)
        ->assertUnprocessable()
        ->assertJsonMissingPath('data.token');
});

it('assigns the client role to the newly registered user', function () use ($payload) {
    $response = $this->postJson('/api/v1/auth/register/client', $payload());
    $response->assertCreated();

    $user = User::where('email', 'jean.dupont@example.com')->first();
    expect($user->hasRole('client'))->toBeTrue();
});

it('hashes the password correctly', function () use ($payload) {
    $data = $payload();
    $this->postJson('/api/v1/auth/register/client', $data)
        ->assertCreated();

    $user = User::where('email', 'jean.dupont@example.com')->first();
    expect($user->password)->not->toBe('Password123!');
    expect(Hash::check('Password123!', $user->password))->toBeTrue();
});

it('returns 422 when email already exists', function () use ($payload) {
    User::factory()->create(['email' => 'existing@example.com']);

    $data = $payload();
    $data['email'] = 'existing@example.com';

    $this->postJson('/api/v1/auth/register/client', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('returns 422 when phone already exists', function () use ($payload) {
    User::factory()->create(['phone' => '+22890111111']);

    $data = $payload();
    $data['phone'] = '+22890111111';

    $this->postJson('/api/v1/auth/register/client', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['phone']);
});

it('returns 422 when required fields are missing', function () {
    $this->postJson('/api/v1/auth/register/client', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'phone', 'password']);
});

it('returns 422 when email format is invalid', function () use ($payload) {
    $data = $payload();
    $data['email'] = 'invalid-email';

    $this->postJson('/api/v1/auth/register/client', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('returns 422 when password is less than 8 characters', function () use ($payload) {
    $data = $payload();
    $data['password'] = 'Pass1';
    $data['password_confirmation'] = 'Pass1';

    $this->postJson('/api/v1/auth/register/client', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

it('returns 422 when password confirmation does not match', function () use ($payload) {
    $data = $payload();
    $data['password'] = 'Password123!';
    $data['password_confirmation'] = 'DifferentPassword123!';

    $this->postJson('/api/v1/auth/register/client', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

it('validates that email is unique in database', function () use ($payload) {
    $data = $payload();
    User::factory()->create(['email' => $data['email']]);

    $this->postJson('/api/v1/auth/register/client', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('validates that phone is unique in database', function () use ($payload) {
    $data = $payload();
    User::factory()->create(['phone' => $data['phone']]);

    $this->postJson('/api/v1/auth/register/client', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['phone']);
});
