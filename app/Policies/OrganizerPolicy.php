<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Screen;
use App\Models\Organizer;
use App\Models\User;
use App\Policies\Concerns\OwnsOrganizerEntity;

final class OrganizerPolicy
{
    use AdminBypassesAll;
    use OwnsOrganizerEntity;

    /**
     * Creating an organizer: a back-office operator (Organizers screen) or a
     * user registering their own organizer profile.
     */
    public function create(User $user): bool
    {
        return $user->can(Screen::ORGANIZERS->permission())
            || $user->hasRole('manager');
    }

    public function delete(User $user, Organizer $organizer): bool
    {
        return $this->update($user, $organizer);
    }

    public function update(User $user, Organizer $organizer): bool
    {
        return $this->screenOrOwner(
            $user,
            Screen::ORGANIZERS->permission(),
            $organizer->user_id
        );
    }
}
