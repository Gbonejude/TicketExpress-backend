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

        // Code de contournement réservé au développement. Il exige un drapeau
        // explicite (OTP_TEST_BYPASS) et reste coupé en production quoi qu'il
        // arrive : dépendre du seul APP_ENV ouvrait n'importe quel compte dès
        // qu'un serveur tournait par erreur en `local`.
        if ($inputCode === '000000' && config('auth.otp_test_bypass') && ! app()->isProduction()) {
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

        // Consommation conditionnelle : deux requêtes simultanées avec le même
        // code ne peuvent pas toutes deux le valider (anti-rejeu).
        $consumed = OtpCode::whereKey($otp->getKey())
            ->whereNull('verified_at')
            ->update(['verified_at' => now()]);

        if ($consumed === 0) {
            throw new ApiException('Invalid or expired OTP code.', 422);
        }

        $user = User::where('phone', $phone)->first();

        return [
            'is_new_user' => $user === null,
            'phone' => $phone,
            'user' => $user,
        ];
    }
}
