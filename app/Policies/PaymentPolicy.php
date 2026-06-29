<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

/**
 * Authorization policy for payment operations.
 *
 * SECURITY P0-1 — Payment authorization:
 * - viewAny: admin/super-admin only (financial data)
 * - view: admin/super-admin OR payment customer OR event organizer
 * - create: system-only (via payment gateway webhooks)
 * - update: forbidden (payments are immutable audit records)
 * - delete: forbidden (payments are immutable audit records)
 *
 * Business rules:
 * - Customers can only view their own payments
 * - Event organizers can view payments for their events
 * - Payments are immutable once created (audit trail)
 * - Super-admin/admin bypass via AdminBypassesAll trait
 */
final class PaymentPolicy
{
    use AdminBypassesAll;

    public function viewAny(User $user): bool
    {
        // Only admin/super-admin can list all payments (financial data)
        // Customers see their payments via Order relationship
        return false;
    }

    public function view(User $user, Payment $payment): bool
    {
        // Customer can view their own payment
        if ((string) $user->id === (string) $payment->order->user_id) {
            return true;
        }

        // Event organizer can view payments for their events
        if ($user->hasRole('organizer-manager') && $user->organizer) {
            $ticket = $payment->order->tickets()->with('ticketType.event')->first();

            if ($ticket && $ticket->ticketType && $ticket->ticketType->event) {
                return (string) $user->organizer->id === (string) $ticket->ticketType->event->organizer_id;
            }
        }

        return false;
    }

    public function create(User $user): bool
    {
        // Payments are created by system via payment gateway webhooks only
        return false;
    }

    public function update(User $user, Payment $payment): bool
    {
        // Payments are immutable audit records
        return false;
    }

    public function delete(User $user, Payment $payment): bool
    {
        // Payments are immutable audit records
        return false;
    }
}
