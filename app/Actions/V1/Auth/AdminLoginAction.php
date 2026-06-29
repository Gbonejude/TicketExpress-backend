<?php

declare(strict_types=1);

namespace App\Actions\V1\Auth;

use App\Actions\Contracts\Action;
use App\Enums\UserRole;
use App\Exceptions\ApiException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminLoginAction implements Action
{
    /**
     * @param  array{email: string, password: string}  $data
     */
    public function execute(array $data): User
    {
        $user = User::where('email', $data['email'])
            // ->where('role', UserRole::Admin)
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw new ApiException('Invalid credentials.', 401);
        }

        return $user;
    }
}
