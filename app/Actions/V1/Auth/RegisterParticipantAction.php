<?php

declare(strict_types=1);

namespace App\Actions\V1\Auth;

use App\Actions\Contracts\Action;
use App\Enums\UserRole;
use App\Exceptions\ApiException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class RegisterParticipantAction implements Action
{
    /**
     * @param  array{
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     *     phone: string,
     * }  $data
     */
    public function execute(array $data): User
    {
        if (User::where('email', $data['email'])->exists()) {
            throw new ApiException('Un compte existe déjà avec cet email.', 409);
        }

        if (User::where('phone', $data['phone'])->exists()) {
            throw new ApiException('Un compte existe déjà avec ce numéro de téléphone.', 409);
        }

        return DB::transaction(function () use ($data): User {
            $password = Str::random(12);

            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($password),
            ]);

            $user->assignRole(UserRole::PARTICIPANT->value);

            return $user;
        });
    }
}
