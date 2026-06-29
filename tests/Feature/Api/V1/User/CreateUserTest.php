<?php

use App\Enums\Gender;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->assignRole(UserRole::SUPER_ADMIN->value);
});

$payload = fn () => [
    'last_name' => 'Doe',
    'first_name' => 'John',
    'email' => 'john.doe'.rand(1, 1000).'@example.com',
    'phone' => '228'.rand(90000000, 99999999),
    'gender' => Gender::MALE->value,
    'role' => UserRole::CLIENT->value,
    'password' => 'password123',
];

it('creates a user with valid data', function () use ($payload) {
    $data = $payload();

    $this->actingAs($this->user)
        ->postJson('/api/v1/users', $data)
        ->assertCreated()
        ->assertJsonPath('data.lastName', 'Doe')
        ->assertJsonPath('data.firstName', 'John');

    $this->assertDatabaseHas('users', [
        'email' => $data['email'],
        'phone' => $data['phone'],
    ]);
});

it('creates a user with an image', function () use ($payload) {
    $data = array_merge($payload(), [
        'image' => UploadedFile::fake()->image('avatar.jpg'),
    ]);

    $this->actingAs($this->user)
        ->postJson('/api/v1/users', $data)
        ->assertCreated();

    $user = User::where('email', $data['email'])->first();
    expect($user->getMedia('users'))->not->toBeEmpty();
});

it('returns 422 when creating user with existing email', function () use ($payload) {
    User::factory()->create(['email' => 'duplicate@example.com']);
    $data = array_merge($payload(), ['email' => 'duplicate@example.com']);

    $this->actingAs($this->user)
        ->postJson('/api/v1/users', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('returns 422 when phone format is invalid', function () use ($payload) {
    $data = array_merge($payload(), ['phone' => 'invalid-phone']);

    $this->actingAs($this->user)
        ->postJson('/api/v1/users', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['phone']);
});
