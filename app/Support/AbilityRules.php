<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Screen;
use App\Models\User;

/**
 * Builds the CASL ability rules the front-end dashboard consumes.
 *
 * The rules mirror the back-end authorization exactly:
 *  - super-admin gets `manage all` (same as the Gate::before bypass);
 *  - every other back-office user gets a baseline `read Auth` (profile,
 *    settings, misc pages) plus one `manage <screen>` rule per screen their
 *    role is allowed to see (`screen.*` permissions).
 *
 * The front uses these rules to hide unauthorized menu entries and to guard
 * routes, so a role that cannot see a screen never renders its menu button.
 */
final class AbilityRules
{
    /**
     * @return array<int, array{action: string, subject: string}>
     */
    public static function for(User $user): array
    {
        if ($user->hasRole('super-admin')) {
            return [['action' => 'manage', 'subject' => 'all']];
        }

        $rules = [
            ['action' => 'read', 'subject' => 'Auth'],
        ];

        foreach (Screen::cases() as $screen) {
            if (! $user->can($screen->permission())) {
                continue;
            }

            // Access to the screen = read/list.
            $rules[] = ['action' => 'read', 'subject' => $screen->value];

            // Per-action grants (create / update / delete) drive button
            // visibility on the front.
            foreach ($screen->actions() as $action) {
                if ($user->can($screen->value.'.'.$action)) {
                    $rules[] = ['action' => $action, 'subject' => $screen->value];
                }
            }
        }

        return $rules;
    }
}
