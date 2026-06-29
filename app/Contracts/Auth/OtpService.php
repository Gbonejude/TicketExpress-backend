<?php

declare(strict_types=1);

namespace App\Contracts\Auth;

use App\Models\OtpCode;

interface OtpService
{
    /**
     * Generate and persist an OTP for a phone number.
     */
    public function generate(string $phone): OtpCode;

    /**
     * Verify the provided OTP code.
     */
    public function verify(string $phone, string $code): bool;
}
