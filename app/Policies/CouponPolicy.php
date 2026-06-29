<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Screen;
use App\Models\Coupon;
use App\Models\User;
use App\Policies\Concerns\OwnsOrganizerEntity;

/**
 * Authorization for Coupon resource.
 *
 * Coupons can be created by admins (screen.coupons) or by event organizers
 * for their own events. An organizer can only manage coupons for events
 * they own.
 */
final class CouponPolicy
{
    use AdminBypassesAll;
    use OwnsOrganizerEntity;

    public function create(User $user): bool
    {
        return $user->can(Screen::COUPONS->permission())
            || $user->hasRole('manager');
    }

    public function delete(User $user, Coupon $coupon): bool
    {
        return $this->update($user, $coupon);
    }

    public function update(User $user, Coupon $coupon): bool
    {
        // Coupons can be managed by admins with screen permission OR
        // the organizer who owns any of the events linked to this coupon
        if ($user->can(Screen::COUPONS->permission())) {
            return true;
        }

        // Check if user is the organizer of any event linked to this coupon
        if ($user->hasRole('manager') && $user->organizer) {
            foreach ($coupon->events as $event) {
                if ((string) $event->organizer_id === (string) $user->organizer->id) {
                    return true;
                }
            }
        }

        return false;
    }

    public function view(User $user, Coupon $coupon): bool
    {
        // Coupons are viewable by admins or organizers who own related events
        if ($user->can(Screen::COUPONS->permission())) {
            return true;
        }

        // Check if user is the organizer of any event linked to this coupon
        if ($user->hasRole('manager') && $user->organizer) {
            foreach ($coupon->events as $event) {
                if ((string) $event->organizer_id === (string) $user->organizer->id) {
                    return true;
                }
            }
        }

        return false;
    }
}
