<?php

use App\Models\OtpCode;
use App\Models\User;

function createVerifiedOtp(string $phone): OtpCode
{
    return OtpCode::create([
        'phone' => $phone,
        'code' => bcrypt('123456'),
        'expire_at' => now()->addMinutes(5),
        'verified_at' => now(),
    ]);
}

$payload = fn (string $phone) => [
    'phone' => $phone,
    'first_name' => 'Jane',
    'last_name' => 'Doe',
];

it('creates a user account and returns a token after OTP verification', function () use ($payload) {
    createVerifiedOtp('+22890100001');

    $this->postJson('/api/v1/auth/register', $payload('+22890100001'))
        ->assertCreated()
        ->assertJson(['success' => true, 'message' => 'Account created successfully.'])
        ->assertJsonStructure(['data' => ['token', 'user']]);

    expect(User::where('phone', '+22890100001')->exists())->toBeTrue();
});

it('returns user data formatted via UserResource after registration', function () use ($payload) {
    createVerifiedOtp('+22890100002');

    $this->postJson('/api/v1/auth/register', $payload('+22890100002'))
        ->assertCreated()
        ->assertJsonStructure(['data' => ['user' => ['id', 'firstName', 'lastName', 'fullName', 'phone']]]);
});

it('returns 422 when phone was not OTP-verified', function () use ($payload) {
    $this->postJson('/api/v1/auth/register', $payload('+22890100004'))
        ->assertStatus(422)
        ->assertJson(['success' => false]);
});

it('returns 422 when the OTP verification session has expired (older than 15 min)', function () use ($payload) {
    OtpCode::create([
        'phone' => '+22890100005',
        'code' => bcrypt('123456'),
        'expires_at' => now()->addMinutes(5),
        'verified_at' => now()->subMinutes(16),
    ]);

    $this->postJson('/api/v1/auth/register', $payload('+22890100005'))
        ->assertStatus(422);
});

it('returns 409 when an account already exists for the phone number', function () use ($payload) {
    User::factory()->create(['phone' => '+22890100006']);
    createVerifiedOtp('+22890100006');

    $this->postJson('/api/v1/auth/register', $payload('+22890100006'))
        ->assertStatus(409);
});

it('returns 422 when required fields are missing', function () {
    createVerifiedOtp('+22890100007');

    $this->postJson('/api/v1/auth/register', ['phone' => '+22890100007'])
        ->assertUnprocessable();
});
