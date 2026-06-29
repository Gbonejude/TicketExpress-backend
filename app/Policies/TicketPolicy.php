<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use App\Policies\Concerns\OwnsOrganizerEntity;

/**
 * Authorization policy for ticket operations.
 *
 * SECURITY P0-1 — Ticket authorization:
 * - viewAny: staff with screen.tickets OR event organizer
 * - view: staff with screen.tickets OR ticket owner OR event organizer
 * - create: system-only (created when order is confirmed)
 * - update: staff with screen.tickets OR event organizer (for status updates)
 * - delete: forbidden (tickets are audit records)
 *
 * Business rules:
 * - Customers can view their own tickets
 * - Event organizers can view/update tickets for their events
 * - Tickets are created automatically when orders are confirmed
 * - Staff can update ticket status (e.g., mark as used, refunded)
 * - Super-admin/admin bypass via AdminBypassesAll trait
 */
final class TicketPolicy
{
    use AdminBypassesAll;
    use OwnsOrganizerEntity;

    public function viewAny(User $user): bool
    {
        // Staff with tickets screen can view all tickets
        if ($user->can('screen.tickets')) {
            return true;
        }

        // Event organizers can view tickets for their events
        // Customers see their tickets via Order relationship
        return $user->hasRole('organizer-manager');
    }

    public function view(User $user, Ticket $ticket): bool
    {
        // Staff with tickets screen can view any ticket
        if ($user->can('screen.tickets')) {
            return true;
        }

        // Customer can view their own ticket
        if ((string) $user->id === (string) $ticket->order->user_id) {
            return true;
        }

        // Event organizer can view tickets for their events
        return $this->screenOrOwner(
            $user,
            'screen.tickets',
            $ticket->ticketType->event->organizer_id
        );
    }

    public function create(User $user): bool
    {
        // Tickets are created automatically by the system when orders are confirmed
        return false;
    }

    public function update(User $user, Ticket $ticket): bool
    {
        // Staff with tickets screen OR event organizers can update ticket status
        return $this->screenOrOwner(
            $user,
            'screen.tickets',
            $ticket->ticketType->event->organizer_id
        );
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        // Tickets are audit records and cannot be deleted
        return false;
    }
}
