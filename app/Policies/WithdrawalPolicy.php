<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Withdrawal;

/**
 * Authorization for Withdrawal resource.
 *
 * Withdrawals are requests by organizers to withdraw earnings from their
 * events. Only the requesting user or admins can view the withdrawal.
 * Processing withdrawals is admin-only.
 */
final class WithdrawalPolicy
{
    use AdminBypassesAll;

    public function create(User $user): bool
    {
        // Organizers with a verified account can request withdrawals
        return $user->hasRole('manager') && $user->organizer !== null;
    }

    public function delete(User $user, Withdrawal $withdrawal): bool
    {
        // Only admins can delete withdrawals (bypass via trait)
        return false;
    }

    /**
     * Process (approve/reject) a withdrawal request.
     * Admin-only action.
     */
    public function process(User $user, Withdrawal $withdrawal): bool
    {
        // Only admins can process withdrawals (bypass via trait)
        return false;
    }

    public function view(User $user, Withdrawal $withdrawal): bool
    {
        // Owner (organizer who made the withdrawal) or admin (admin bypass via trait)
        return $this->isOwner($user, $withdrawal);
    }

    private function isOwner(User $user, Withdrawal $withdrawal): bool
    {
        // Check if the user is the organizer who made this withdrawal
        return $user->organizer !== null
            && (string) $withdrawal->organizer_id === (string) $user->organizer->id;
    }
}
