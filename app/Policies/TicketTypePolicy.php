<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Screen;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use App\Policies\Concerns\OwnsOrganizerEntity;

/**
 * Authorization for TicketType resource.
 *
 * Ticket types belong to events. Only admins with screen.tickets permission
 * or the organizer who owns the event can create, update, or delete
 * ticket types.
 */
final class TicketTypePolicy
{
    use AdminBypassesAll;
    use OwnsOrganizerEntity;

    public function create(User $user, ?Event $event = null): bool
    {
        return $this->screenOrOwner(
            $user,
            Screen::TICKETS->permission(),
            $event?->organizer?->user_id
        );
    }

    public function delete(User $user, TicketType $ticketType): bool
    {
        return $this->update($user, $ticketType);
    }

    public function update(User $user, TicketType $ticketType): bool
    {
        return $this->screenOrOwner(
            $user,
            Screen::TICKETS->permission(),
            $ticketType->event?->organizer?->user_id
        );
    }

    public function view(User $user, TicketType $ticketType): bool
    {
        // Ticket types are public (visible on event pages)
        return true;
    }
}
