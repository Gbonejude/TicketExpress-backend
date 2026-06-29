<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

/**
 * Authorization for Review resource.
 *
 * SECURITY V2-3 — BOLA protection: reviews can only be deleted by their
 * author or by admins. Any authenticated user can create a review, but
 * cannot modify or delete reviews written by others.
 */
final class ReviewPolicy
{
    use AdminBypassesAll;

    public function create(User $user): bool
    {
        // Any authenticated user can create a review
        return true;
    }

    public function delete(User $user, Review $review): bool
    {
        // Only the author or admin can delete (admin bypass via trait)
        return $this->isAuthor($user, $review);
    }

    public function update(User $user, Review $review): bool
    {
        // Only the author or admin can update (admin bypass via trait)
        return $this->isAuthor($user, $review);
    }

    public function view(User $user, Review $review): bool
    {
        // Reviews are public
        return true;
    }

    private function isAuthor(User $user, Review $review): bool
    {
        return (string) $review->user_id === (string) $user->id;
    }
}
