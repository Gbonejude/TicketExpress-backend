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
        // Un organisateur crée ses coupons dès lors que son compte porte un
        // organisateur ; l'administration passe par la permission d'écran.
        // (Le rôle testé était `manager`, qui n'existe pas — le rôle réel est
        // `organizer-manager` —, donc cette branche ne s'ouvrait jamais.)
        return $user->hasRole('organizer-manager')
            ? $user->organizer !== null
            : $user->can(Screen::COUPONS->permission());
    }

    public function delete(User $user, Coupon $coupon): bool
    {
        return $this->update($user, $coupon);
    }

    public function update(User $user, Coupon $coupon): bool
    {
        // Un organisateur n'administre qu'un coupon rattaché à l'un de ses
        // événements, même s'il détient `screen.coupons` : la permission ouvre
        // l'écran, pas les coupons des confrères. L'administration est déjà
        // passée par {@see AdminBypassesAll::before()}.
        if ($user->hasRole('organizer-manager')) {
            return $this->ownsAnyEvent($user, $coupon);
        }

        return $user->can(Screen::COUPONS->permission());
    }

    public function view(User $user, Coupon $coupon): bool
    {
        return $this->update($user, $coupon);
    }

    /** Vrai si l'un des événements du coupon appartient à l'organisateur du compte. */
    private function ownsAnyEvent(User $user, Coupon $coupon): bool
    {
        if ($user->organizer === null) {
            return false;
        }

        return $coupon->events->contains(
            fn ($event): bool => (string) $event->organizer_id === (string) $user->organizer->id
        );
    }
}
