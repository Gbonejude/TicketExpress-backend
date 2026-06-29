<?php

declare(strict_types=1);

namespace App\Actions\V1\Auth;

use App\Actions\Contracts\Action;
use App\Exceptions\ApiException;
use App\Models\User;
use Illuminate\Support\Facades\Password;

class ForgotPasswordAction implements Action
{
    /**
     * @param  array{email: string}  $data
     */
    public function execute(array $data): mixed
    {
        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            return true;
        }

        $status = Password::sendResetLink(['email' => $data['email']]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw new ApiException('Unable to send reset link. Please try again.', 500);
        }

        return true;
    }
}
