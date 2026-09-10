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
        // Un organisateur reste borné à ses propres entités, même lorsqu'il
        // détient la permission d'écran : celle-ci lui ouvre l'écran, elle ne lui
        // donne pas les données des confrères. Sans cette borne, `can(screen.*)`
        // était vrai pour tout organisateur et la branche propriétaire — jamais
        // atteinte — ne servait à rien. L'administration, elle, ne passe pas ici :
        // {@see AdminBypassesAll::before()} l'autorise avant la policy.
        if ($user->hasRole('organizer-manager')) {
            return $ownerUserId !== null
                && (string) $user->id === (string) $ownerUserId;
        }

        return $user->can($screenPermission);
    }
}
