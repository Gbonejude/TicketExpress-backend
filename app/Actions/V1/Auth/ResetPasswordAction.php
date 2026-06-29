<?php

declare(strict_types=1);

namespace App\Actions\V1\Auth;

use App\Actions\Contracts\Action;
use App\Exceptions\ApiException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class ResetPasswordAction implements Action
{
    /**
     * @param  array{
     *     email: string,
     *     token: string,
     *     password: string,
     * }  $data
     */
    public function execute(array $data): mixed
    {
        $status = Password::reset(
            [
                'email' => $data['email'],
                'token' => $data['token'],
                'password' => $data['password'],
                'password_confirmation' => $data['password'],
            ],
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
                $user->tokens()->delete(); // révoque tous les tokens existants
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw new ApiException(match ($status) {
                Password::INVALID_TOKEN => 'Invalid or expired reset token.',
                Password::INVALID_USER => 'No account found for this email.',
                default => 'Unable to reset password.',
            }, 422);
        }

        return true;
    }
}
