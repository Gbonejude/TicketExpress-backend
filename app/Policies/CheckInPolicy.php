<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CheckIn;
use App\Models\User;
use App\Policies\Concerns\OwnsOrganizerEntity;

/**
 * Authorization policy for check-in operations.
 *
 * SECURITY P0-1 — Check-in authorization:
 * - viewAny: staff with screen.tickets OR event organizer
 * - view: staff with screen.tickets OR event organizer
 * - create: staff with screen.tickets OR event organizer (to manually check-in guests)
 * - update: forbidden (check-ins are immutable once created)
 * - delete: forbidden (check-ins are audit records)
 *
 * Business rules:
 * - Only event organizers or staff with tickets screen can check-in guests
 * - Check-ins are immutable audit records (no update/delete)
 * - Super-admin/admin bypass via AdminBypassesAll trait
 */
final class CheckInPolicy
{
    use AdminBypassesAll;
    use OwnsOrganizerEntity;

    public function viewAny(User $user): bool
    {
        // Staff with tickets screen can view all check-ins
        if ($user->can('screen.tickets')) {
            return true;
        }

        // Event organizers can view check-ins for their events
        return $user->hasRole('organizer-manager');
    }

    public function view(User $user, CheckIn $checkIn): bool
    {
        // Staff with tickets screen can view any check-in
        if ($user->can('screen.tickets')) {
            return true;
        }

        // Event organizers can view check-ins for their events
        return $this->screenOrOwner(
            $user,
            'screen.tickets',
            $checkIn->ticket->ticketType->event->organizer?->user_id
        );
    }

    public function create(User $user): bool
    {
        // Staff with tickets screen OR event organizers can create check-ins
        return $user->can('screen.tickets') || $user->hasRole('organizer-manager');
    }

    public function update(User $user, CheckIn $checkIn): bool
    {
        // Check-ins are immutable audit records
        return false;
    }

    public function delete(User $user, CheckIn $checkIn): bool
    {
        // Check-ins are immutable audit records
        return false;
    }
}
