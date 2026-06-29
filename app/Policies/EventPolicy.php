<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Screen;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\User;
use App\Policies\Concerns\OwnsOrganizerEntity;

final class EventPolicy
{
    use AdminBypassesAll;
    use OwnsOrganizerEntity;

    public function create(User $user, ?Organizer $organizer = null): bool
    {
        return $this->screenOrOwner(
            $user,
            Screen::EVENTS->permission(),
            $organizer?->user_id
        );
    }

    public function delete(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }

    public function update(User $user, Event $event): bool
    {
        return $this->screenOrOwner(
            $user,
            Screen::EVENTS->permission(),
            $event->organizer?->user_id
        );
    }
}
