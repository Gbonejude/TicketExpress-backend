<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Notification;

final class NotificationHelper
{
    /**
     * Notify all administrators (admin and super-admin roles).
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     */
    public static function notifyAdmins($notification): void
    {
        $admins = User::role(['admin', 'super-admin'])->get();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, $notification);
        }
    }

    /**
     * Notify all super-admins only.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     */
    public static function notifySuperAdmins($notification): void
    {
        $superAdmins = User::role('super-admin')->get();
        if ($superAdmins->isNotEmpty()) {
            Notification::send($superAdmins, $notification);
        }
    }

    /**
     * Notify all managers (organizer-manager role).
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     */
    public static function notifyManagers($notification): void
    {
        $managers = User::role('organizer-manager')->get();
        if ($managers->isNotEmpty()) {
            Notification::send($managers, $notification);
        }
    }

    /**
     * Notify specific organizer manager.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     */
    public static function notifyOrganizerManager(User $organizerUser, $notification): void
    {
        if ($organizerUser->hasRole('organizer-manager')) {
            $organizerUser->notify($notification);
        }
    }

    /**
     * Notify client (user with client role).
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     */
    public static function notifyClient(User $client, $notification): void
    {
        if ($client->hasRole('client')) {
            $client->notify($notification);
        }
    }

    /**
     * Notify all users with specific role.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     */
    public static function notifyByRole(string $role, $notification): void
    {
        $users = User::role($role)->get();
        if ($users->isNotEmpty()) {
            Notification::send($users, $notification);
        }
    }

    /**
     * Notify specific user.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     */
    public static function notifyUser(User $user, $notification): void
    {
        $user->notify($notification);
    }
}
