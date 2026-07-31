<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$credentials = [
    'email' => 'jean.dupont@example.com',
    'password' => 'Password123!',
];

$makeClient = fn (): User => User::factory()->create([
    'email' => 'jean.dupont@example.com',
    'password' => Hash::make('Password123!'),
]);

it('authenticates a client and returns a token with the user', function () use ($credentials, $makeClient) {
    $makeClient();

    $response = $this->postJson('/api/v1/auth/login', $credentials)
        ->assertSuccessful()
        ->assertJson(['success' => true, 'message' => 'Connexion réussie.'])
        ->assertJsonStructure([
            'data' => [
                'token',
                'user' => ['id', 'firstName', 'lastName', 'fullName', 'email', 'phone'],
            ],
        ]);

    expect($response->json('data.token'))->toBeString()->not->toBeEmpty();
});

it('issues a token that authenticates subsequent requests', function () use ($credentials, $makeClient) {
    $makeClient();

    $token = $this->postJson('/api/v1/auth/login', $credentials)->json('data.token');

    $this->withToken($token)
        ->getJson('/api/v1/me')
        ->assertSuccessful();
});

it('names the token after the X-Device-Name header', function () use ($credentials, $makeClient) {
    $user = $makeClient();

    $this->withHeader('X-Device-Name', 'iphone-safari')
        ->postJson('/api/v1/auth/login', $credentials)
        ->assertSuccessful();

    expect($user->fresh()->tokens()->first()->name)->toBe('iphone-safari');
});

it('falls back to the "web" device name when the header is absent', function () use ($credentials, $makeClient) {
    $user = $makeClient();

    $this->postJson('/api/v1/auth/login', $credentials)->assertSuccessful();

    expect($user->fresh()->tokens()->first()->name)->toBe('web');
});

it('returns 401 when the password is wrong', function () use ($makeClient) {
    $makeClient();

    $this->postJson('/api/v1/auth/login', [
        'email' => 'jean.dupont@example.com',
        'password' => 'WrongPassword123!',
    ])->assertUnauthorized();
});

it('returns 401 when the email is unknown', function () use ($credentials) {
    $this->postJson('/api/v1/auth/login', $credentials)
        ->assertUnauthorized();
});

it('does not reveal whether an email is registered', function () use ($makeClient) {
    $makeClient();

    $wrongPassword = $this->postJson('/api/v1/auth/login', [
        'email' => 'jean.dupont@example.com',
        'password' => 'WrongPassword123!',
    ]);

    $unknownEmail = $this->postJson('/api/v1/auth/login', [
        'email' => 'nobody@example.com',
        'password' => 'WrongPassword123!',
    ]);

    expect($wrongPassword->json('message'))->toBe($unknownEmail->json('message'));
});

it('returns 422 when required fields are missing', function () {
    $this->postJson('/api/v1/auth/login', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);
});

it('returns 422 when the email format is invalid', function () {
    $this->postJson('/api/v1/auth/login', [
        'email' => 'not-an-email',
        'password' => 'Password123!',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('does not return back-office ability rules', function () use ($credentials, $makeClient) {
    $makeClient();

    $this->postJson('/api/v1/auth/login', $credentials)
        ->assertSuccessful()
        ->assertJsonMissingPath('data.userAbilityRules');
});

it('still lets the back-office log in through admin/login', function () use ($credentials, $makeClient) {
    $makeClient();

    $this->postJson('/api/v1/auth/admin/login', $credentials)
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['accessToken', 'userData', 'userAbilityRules']]);
});
