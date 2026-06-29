<?php

declare(strict_types=1);

namespace App\Contracts\Auth;

use App\Models\User;

interface AuthenticationService
{
    /**
     * Authenticate a user with email and password.
     */
    public function loginWithPassword(string $email, string $password): User;

    /**
     * Authenticate a user using OTP.
     */
    public function loginWithOtp(string $phone, string $otp): User;

    /**
     * Log out the currently authenticated user.
     */
    public function logout(): void;
}
