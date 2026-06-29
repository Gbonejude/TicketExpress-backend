<?php

declare(strict_types=1);

namespace App\Actions\V1\Auth;

use App\Actions\Contracts\Action;
use App\Contracts\Auth\OtpGenerator;
use App\Contracts\Auth\OtpSender;
use App\Exceptions\ApiException;
use App\Models\OtpCode;

class SendOtpAction implements Action
{
    public function __construct(
        private readonly OtpSender $otpSender,
        private readonly OtpGenerator $otpGenerator,
    ) {}

    /**
     * @param  array{phone: string}  $data
     */
    public function execute(array $data): mixed
    {
        $phone = $data['phone'];
        $latest = OtpCode::where('phone', $phone)->latest()->first();

        if ($latest?->isOnCooldown()) {
            throw new ApiException('Please wait 60 seconds before requesting a new OTP.', 429);
        }

        $code = $this->otpGenerator->generate();

        OtpCode::create([
            'phone' => $phone,
            'code' => bcrypt($code),
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->otpSender->send($phone, $code);

        return true;
    }
}
