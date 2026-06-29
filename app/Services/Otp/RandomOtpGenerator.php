<?php

declare(strict_types=1);

namespace App\Services\Otp;

use App\Contracts\Auth\OtpGenerator;

class RandomOtpGenerator implements OtpGenerator
{
    public function generate(int $digits = 6): string
    {
        $max = (int) str_repeat('9', $digits);

        return str_pad((string) random_int(0, $max), $digits, '0', STR_PAD_LEFT);
    }
}
