<?php

use App\Models\OtpCode;
use App\Models\User;

function createOtp(string $phone, array $overrides = []): OtpCode
{
    return OtpCode::create(array_merge([
        'phone' => $phone,
        'code' => bcrypt('123456'),
        'expires_at' => now()->addMinutes(5),
    ], $overrides));
}

it('returns a token when OTP is valid for an existing user', function () {
    User::factory()->create(['phone' => '+22890000001']);
    createOtp('+22890000001');

    $this->postJson('/api/v1/auth/verify-otp', ['phone' => '+22890000001', 'code' => '123456'])
        ->assertSuccessful()
        ->assertJson(['data' => ['is_new_user' => false]])
        ->assertJsonStructure(['data' => ['token', 'user']]);
});

it('returns user data formatted via UserResource for an existing user', function () {
    User::factory()->create(['phone' => '+22890000001']);
    createOtp('+22890000001');

    $this->postJson('/api/v1/auth/verify-otp', ['phone' => '+22890000001', 'code' => '123456'])
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['user' => ['id', 'firstName', 'lastName', 'fullName', 'phone']]]);
});

it('returns is_new_user true for an unregistered phone number', function () {
    createOtp('+22890000002');

    $this->postJson('/api/v1/auth/verify-otp', ['phone' => '+22890000002', 'code' => '123456'])
        ->assertSuccessful()
        ->assertJson(['data' => ['is_new_user' => true, 'phone' => '+22890000002']]);
});

it('marks the OTP as verified after a successful check', function () {
    createOtp('+22890000003');

    $this->postJson('/api/v1/auth/verify-otp', ['phone' => '+22890000003', 'code' => '123456']);

    expect(OtpCode::where('phone', '+22890000003')->whereNotNull('verified_at')->exists())->toBeTrue();
});

it('returns 422 for an incorrect OTP code', function () {
    createOtp('+22890000004');

    $this->postJson('/api/v1/auth/verify-otp', ['phone' => '+22890000004', 'code' => '999999'])
        ->assertStatus(422)
        ->assertJson(['success' => false]);
});

it('increments the attempts counter on each wrong code', function () {
    createOtp('+22890000005');

    $this->postJson('/api/v1/auth/verify-otp', ['phone' => '+22890000005', 'code' => '999999']);
    $this->postJson('/api/v1/auth/verify-otp', ['phone' => '+22890000005', 'code' => '999999']);

    expect(OtpCode::where('phone', '+22890000005')->first()->attempts)->toBe(2);
});

it('returns 422 for an expired OTP', function () {
    createOtp('+22890000006', ['expires_at' => now()->subMinute()]);

    $this->postJson('/api/v1/auth/verify-otp', ['phone' => '+22890000006', 'code' => '123456'])
        ->assertStatus(422);
});

it('returns 422 when OTP is blocked after reaching max attempts', function () {
    createOtp('+22890000007', ['attempts' => 5]);

    $this->postJson('/api/v1/auth/verify-otp', ['phone' => '+22890000007', 'code' => '123456'])
        ->assertStatus(422);
});

it('returns 422 when no OTP exists for the phone', function () {
    $this->postJson('/api/v1/auth/verify-otp', ['phone' => '+22890000008', 'code' => '123456'])
        ->assertStatus(422);
});

it('returns 422 when fields are missing', function () {
    $this->postJson('/api/v1/auth/verify-otp', [])
        ->assertUnprocessable();
});

it('refuses to validate the same OTP twice', function () {
    createOtp('+22890000009');
    $payload = ['phone' => '+22890000009', 'code' => '123456'];

    $this->postJson('/api/v1/auth/verify-otp', $payload)->assertOk();
    $this->postJson('/api/v1/auth/verify-otp', $payload)->assertStatus(422);
});

it('ignores the 000000 bypass code unless OTP_TEST_BYPASS is set', function () {
    config(['auth.otp_test_bypass' => false]);
    createOtp('+22890000010');

    $this->postJson('/api/v1/auth/verify-otp', ['phone' => '+22890000010', 'code' => '000000'])
        ->assertStatus(422);
});

it('accepts the 000000 bypass code when the flag is set outside production', function () {
    config(['auth.otp_test_bypass' => true]);

    $this->postJson('/api/v1/auth/verify-otp', ['phone' => '+22890000011', 'code' => '000000'])
        ->assertOk();
});

it('never accepts the bypass code in production, even with the flag set', function () {
    config(['auth.otp_test_bypass' => true]);
    app()->detectEnvironment(fn (): string => 'production');

    $this->postJson('/api/v1/auth/verify-otp', ['phone' => '+22890000012', 'code' => '000000'])
        ->assertStatus(422);
});
