<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

/**
 * Authorization for Order resource.
 *
 * SECURITY V2-3 — BOLA (Broken Object Level Authorization) protection:
 * OrderController::show / update / destroy used to do Order::findOrFail($id)
 * with no ownership check. Any authenticated client could read or cancel
 * any other client's order by guessing the ULID.
 *
 * Rules implemented:
 *   - admins (admin/super-admin) bypass everything.
 *   - the customer who placed it can view, update non-financial fields,
 *     and cancel their own order.
 *   - organizers can view orders for their events (needed for order management).
 */
final class OrderPolicy
{
    use AdminBypassesAll;

    public function delete(User $user, Order $order): bool
    {
        // Equivalent to cancel: only the customer who created the order.
        return $this->isOwner($user, $order);
    }

    public function update(User $user, Order $order): bool
    {
        // The generic PUT /orders/{id} is intended for the customer to amend
        // their own pending order (note, details, etc.).
        return $this->isOwner($user, $order);
    }

    public function view(User $user, Order $order): bool
    {
        return $this->isOwner($user, $order)
            || $this->isEventOrganizer($user, $order);
    }

    private function isEventOrganizer(User $user, Order $order): bool
    {
        // Check if the user is the organizer of any event for tickets in this order
        if (! $user->hasRole('manager') || ! $user->organizer) {
            return false;
        }

        // Get the first ticket's event to check organizer
        $ticket = $order->tickets()->with('ticketType.event')->first();

        if (! $ticket || ! $ticket->ticketType || ! $ticket->ticketType->event) {
            return false;
        }

        $event = $ticket->ticketType->event;

        return $event->organizer
            && (string) $event->organizer->user_id === (string) $user->id;
    }

    private function isOwner(User $user, Order $order): bool
    {
        return (string) $order->user_id === (string) $user->id;
    }
}
