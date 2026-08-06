<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use App\Models\User;

trait OwnsOrganizerEntity
{
    /**
     * Autorise si l'utilisateur a la permission d'écran, ou s'il est le
     * propriétaire de l'entité.
     *
     * `$ownerUserId` est un identifiant de **compte** (`organizers.user_id`), pas
     * d'organisateur. Le nom du paramètre le dit désormais : trois appelants
     * (TicketPolicy::view/update, CheckInPolicy::view) passaient
     * `event->organizer_id`, soit un id d'organisateur, et la branche
     * propriétaire ne pouvait donc jamais être vraie — seule la permission
     * d'écran laissait passer.
     *
     * @param  User  $user  The user performing the action
     * @param  string  $screenPermission  The permission to check
     * @param  int|string|null  $ownerUserId  `organizers.user_id` of the owning organizer
     * @return bool True if user has permission or owns the entity
     */
    protected function screenOrOwner(User $user, string $screenPermission, int|string|null $ownerUserId): bool
    {
        if ($user->can($screenPermission)) {
            return true;
        }

        return $ownerUserId !== null
            && $user->hasRole('organizer-manager')
            && (string) $user->id === (string) $ownerUserId;
    }
}
