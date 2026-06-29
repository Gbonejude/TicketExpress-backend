<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Screen;
use App\Models\EventCategory;
use App\Models\User;

/**
 * Authorization for EventCategory resource.
 *
 * Event categories are global system-wide classifications (Concert, Festival,
 * Theatre, etc.). Only admins with the screen.categories permission can
 * create, update, or delete categories.
 */
final class EventCategoryPolicy
{
    use AdminBypassesAll;

    public function create(User $user): bool
    {
        return $user->can(Screen::CATEGORIES->permission());
    }

    public function delete(User $user, EventCategory $category): bool
    {
        return $this->update($user, $category);
    }

    public function update(User $user, EventCategory $category): bool
    {
        return $user->can(Screen::CATEGORIES->permission());
    }

    public function view(User $user, EventCategory $category): bool
    {
        // Categories are public
        return true;
    }
}
