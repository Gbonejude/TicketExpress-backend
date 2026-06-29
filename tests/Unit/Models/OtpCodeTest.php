<?php

use App\Models\OtpCode;

function makeOtp(array $attrs = []): OtpCode
{
    return OtpCode::create(array_merge([
        'phone' => '+22890'.rand(100000, 999999),
        'code' => bcrypt('123456'),
        'expires_at' => now()->addMinutes(5),
    ], $attrs));
}

// --- scopeValid ---

it('includes a valid non-expired, non-verified, non-blocked OTP in the valid scope', function () {
    makeOtp();

    expect(OtpCode::valid()->count())->toBe(1);
});

it('excludes expired OTPs from the valid scope', function () {
    makeOtp(['expires_at' => now()->subMinute()]);

    expect(OtpCode::valid()->count())->toBe(0);
});

it('excludes already-verified OTPs from the valid scope', function () {
    makeOtp(['verified_at' => now()]);

    expect(OtpCode::valid()->count())->toBe(0);
});

it('excludes OTPs that have reached the max attempts from the valid scope', function () {
    makeOtp(['attempts' => 5]);

    expect(OtpCode::valid()->count())->toBe(0);
});

// --- isOnCooldown ---

it('returns true for isOnCooldown when created less than 60 seconds ago', function () {
    $otp = makeOtp();

    expect($otp->isOnCooldown())->toBeTrue();
});

it('returns false for isOnCooldown when created more than 60 seconds ago', function () {
    $otp = makeOtp();
    OtpCode::where('id', $otp->id)->update(['created_at' => now()->subSeconds(61)]);

    expect($otp->fresh()->isOnCooldown())->toBeFalse();
});

// --- isBlocked ---

it('returns true for isBlocked when attempts is 5', function () {
    $otp = makeOtp(['attempts' => 5]);

    expect($otp->isBlocked())->toBeTrue();
});

it('returns false for isBlocked when attempts is below 5', function () {
    $otp = makeOtp(['attempts' => 4]);

    expect($otp->isBlocked())->toBeFalse();
});

// --- isExpired ---

it('returns true for isExpired when expires_at is in the past', function () {
    $otp = makeOtp(['expires_at' => now()->subMinute()]);

    expect($otp->isExpired())->toBeTrue();
});

it('returns false for isExpired when expires_at is in the future', function () {
    $otp = makeOtp();

    expect($otp->isExpired())->toBeFalse();
});
