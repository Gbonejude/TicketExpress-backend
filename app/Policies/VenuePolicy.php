<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Screen;
use App\Models\User;
use App\Models\Venue;

/**
 * Authorization for Venue resource.
 *
 * Venues are locations where events take place. Only admins with the
 * screen.venues permission can create, update, or delete venues.
 */
final class VenuePolicy
{
    use AdminBypassesAll;

    public function create(User $user): bool
    {
        return $user->can(Screen::VENUES->permission());
    }

    public function delete(User $user, Venue $venue): bool
    {
        return $this->update($user, $venue);
    }

    public function update(User $user, Venue $venue): bool
    {
        return $user->can(Screen::VENUES->permission());
    }

    public function view(User $user, Venue $venue): bool
    {
        // Venues are public
        return true;
    }
}
