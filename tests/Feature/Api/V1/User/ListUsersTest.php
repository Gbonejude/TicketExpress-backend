<?php

use App\Enums\UserRole;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->assignRole(UserRole::SUPER_ADMIN->value);
});

it('lists users for an authenticated user', function () {
    User::factory()->count(3)->create();

    $this->actingAs($this->user)
        ->getJson('/api/v1/users')
        ->assertOk()
        ->assertJsonStructure(['data' => [['id', 'lastName', 'firstName', 'email', 'phone', 'gender', 'role']]]);
});

it('returns 401 when unauthenticated', function () {
    $this->getJson('/api/v1/users')
        ->assertUnauthorized();
});
