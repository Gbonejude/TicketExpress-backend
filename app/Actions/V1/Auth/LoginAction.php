<?php

declare(strict_types=1);

namespace App\Actions\V1\Auth;

use App\Actions\Contracts\Action;
use App\Exceptions\ApiException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Authenticates a user with an email address and a password.
 *
 * Shared by the public site and the back-office: both verify the same
 * credentials, and only differ in what they return alongside the token.
 */
final class LoginAction implements Action
{
    /**
     * @param  array{email: string, password: string}  $data
     *
     * @throws ApiException when the credentials do not match an account.
     */
    public function execute(array $data): User
    {
        $user = User::where('email', $data['email'])->first();

        // A single message for both branches: telling the caller that an email
        // exists but the password is wrong would confirm registered addresses.
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw new ApiException('E-mail ou mot de passe incorrect.', 401);
        }

        return $user;
    }
}
