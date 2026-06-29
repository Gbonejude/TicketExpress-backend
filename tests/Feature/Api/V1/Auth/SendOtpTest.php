<?php

use App\Contracts\Auth\OtpGenerator;
use App\Contracts\Auth\OtpSender;
use App\Models\OtpCode;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->mock(OtpSender::class)->shouldReceive('send')->andReturn(null);
    $this->mock(OtpGenerator::class)->shouldReceive('generate')->andReturn('123456');
});

it('sends an OTP for a valid Togolese phone number', function () {
    $this->postJson('/api/v1/auth/send-otp', ['phone' => '+22890123456'])
        ->assertSuccessful()
        ->assertJson(['success' => true, 'message' => 'OTP sent successfully.']);

    expect(OtpCode::where('phone', '+22890123456')->exists())->toBeTrue();
});

it('stores the OTP hashed in the database', function () {
    $this->postJson('/api/v1/auth/send-otp', ['phone' => '+22890123456']);

    $otp = OtpCode::where('phone', '+22890123456')->first();

    expect($otp)->not->toBeNull()
        ->and($otp->code)->not->toBe('123456')
        ->and(Hash::check('123456', $otp->code))->toBeTrue();
});

it('sets the OTP expiry to 5 minutes', function () {
    $this->postJson('/api/v1/auth/send-otp', ['phone' => '+22890123456']);

    $otp = OtpCode::where('phone', '+22890123456')->first();

    expect($otp->expires_at)->toBeBetween(now()->addMinutes(4), now()->addMinutes(6));
});

it('returns 429 when a second OTP is requested within the cooldown period', function () {
    $this->postJson('/api/v1/auth/send-otp', ['phone' => '+22890123456'])
        ->assertSuccessful();

    $this->postJson('/api/v1/auth/send-otp', ['phone' => '+22890123456'])
        ->assertStatus(429)
        ->assertJson(['success' => false]);
});

it('allows a new OTP after the cooldown period has passed', function () {
    OtpCode::create([
        'phone' => '+22890123456',
        'code' => bcrypt('999999'),
        'expires_at' => now()->addMinutes(5),
    ]);

    OtpCode::where('phone', '+22890123456')
        ->update(['created_at' => now()->subSeconds(61)]);

    $this->postJson('/api/v1/auth/send-otp', ['phone' => '+22890123456'])
        ->assertSuccessful();
});

it('returns 422 for an invalid phone format', function () {
    $this->postJson('/api/v1/auth/send-otp', ['phone' => '0123456789'])
        ->assertUnprocessable();
});

it('returns 422 for a phone number with wrong country code', function () {
    $this->postJson('/api/v1/auth/send-otp', ['phone' => '+33612345678'])
        ->assertUnprocessable();
});

it('returns 422 when phone is missing', function () {
    $this->postJson('/api/v1/auth/send-otp', [])
        ->assertUnprocessable();
});
