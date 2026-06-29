<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class UserPolicy
{
    use AdminBypassesAll;

    /**
     * Determine whether the user can delete another user.
     *
     * @param  User  $user  The user performing the action
     * @param  User  $target  The user being deleted
     */
    public function delete(User $user, User $target): bool
    {
        return false;
    }

    /**
     * Determine whether the user can toggle availability of another user.
     *
     * @param  User  $user  The user performing the action
     * @param  User  $target  The user whose availability is being toggled
     */
    public function toggleAvailability(User $user, User $target): bool
    {
        return $this->isSelf($user, $target);
    }

    /**
     * Determine whether the user can update another user.
     *
     * @param  User  $user  The user performing the action
     * @param  User  $target  The user being updated
     */
    public function update(User $user, User $target): bool
    {
        return $this->isSelf($user, $target);
    }

    /**
     * Determine whether the user can view another user.
     *
     * @param  User  $user  The user performing the action
     * @param  User  $target  The user being viewed
     */
    public function view(User $user, User $target): bool
    {
        return $this->isSelf($user, $target);
    }

    /**
     * Check if the user is attempting to act on themselves.
     *
     * @param  User  $user  The user performing the action
     * @param  User  $target  The target user
     */
    private function isSelf(User $user, User $target): bool
    {
        return (string) $user->id === (string) $target->id;
    }
}
