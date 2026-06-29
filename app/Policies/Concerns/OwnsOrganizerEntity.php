<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use App\Models\User;

trait OwnsOrganizerEntity
{
    /**
     * Check if the user has screen permission or owns the entity.
     *
     * @param  User  $user  The user performing the action
     * @param  string  $screenPermission  The permission to check
     * @param  int|string|null  $ownerId  The owner ID of the entity
     * @return bool True if user has permission or owns the entity
     */
    protected function screenOrOwner(User $user, string $screenPermission, int|string|null $ownerId): bool
    {
        if ($user->can($screenPermission)) {
            return true;
        }

        return $ownerId !== null
            && $user->hasRole('organizer-manager')
            && (string) $user->id === (string) $ownerId;
    }
}
