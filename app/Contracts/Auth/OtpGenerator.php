<?php

declare(strict_types=1);

namespace App\Contracts\Auth;

/**
 * Contract for OTP code generation.
 * Implement this interface to swap generation strategies (e.g. alphanumeric, TOTP).
 */
interface OtpGenerator
{
    /**
     * Generate a numeric OTP code of the given length.
     */
    public function generate(int $digits = 6): string;
}
