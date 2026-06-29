<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

trait AdminBypassesAll
{
    /**
     * Allow admins and super-admins to bypass all policy checks.
     *
     * @param  User  $user  The user performing the action
     * @param  string  $ability  The ability being checked
     * @return bool|null True to allow, null to continue with policy checks
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return true;
        }

        return null;
    }
}
