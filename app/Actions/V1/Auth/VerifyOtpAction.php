<?php

namespace App\Actions\V1\Auth;

use App\Actions\Contracts\Action;
use App\Exceptions\ApiException;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class VerifyOtpAction implements Action
{
    /**
     * @param  array{phone: string, code: string}  $data
     * @return array{is_new_user: bool, phone: string, user: User|null}
     */
    public function execute(array $data): mixed
    {
        $phone = $data['phone'];
        $inputCode = $data['code'];

        // Bypass OTP verification in local/testing environments
        if (app()->environment(['local', 'testing']) && $inputCode === '000000') {
            OtpCode::where('phone', $phone)->delete();

            $user = User::where('phone', $phone)->first();

            return [
                'is_new_user' => $user === null,
                'phone' => $phone,
                'user' => $user,
            ];
        }

        $otp = OtpCode::valid()->where('phone', $phone)->latest()->first();

        if (! $otp) {
            throw new ApiException('Invalid or expired OTP code.', 422);
        }

        if (! Hash::check($inputCode, $otp->code)) {
            $otp->increment('attempts');

            $remaining = 5 - $otp->attempts;

            throw new ApiException(
                $remaining > 0
                    ? "Incorrect OTP code. {$remaining} attempt(s) remaining."
                    : 'OTP blocked. Please request a new one.',
                422,
            );
        }

        $otp->update(['verified_at' => now()]);

        $user = User::where('phone', $phone)->first();

        return [
            'is_new_user' => $user === null,
            'phone' => $phone,
            'user' => $user,
        ];
    }
}
