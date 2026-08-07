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
     * @return array<int, array{action: string, subject: string, inverted?: bool}>
     */
    public static function for(User $user): array
    {
        if ($user->hasRole('super-admin')) {
            $rules = [['action' => 'manage', 'subject' => 'all']];

            // `manage all` couvrirait aussi la fiche d'organisateur, alors que
            // le super-admin n'en a pas : l'écran « Contrôle d'accès » lui
            // afficherait un formulaire sans objet, et l'API lui répondrait 404.
            // CASL retient la dernière règle applicable, donc une règle inversée
            // posée après suffit à la lui retirer — sans toucher au reste de ses
            // droits, et sans la lui retirer s'il organise lui-même.
            if ($user->organizer === null) {
                $rules[] = ['action' => 'read', 'subject' => 'organizer-profile', 'inverted' => true];
            }

            return $rules;
        }

        $rules = [
            ['action' => 'read', 'subject' => 'Auth'],
        ];

        // Sa propre fiche d'organisateur — notamment les heures d'ouverture du
        // contrôle d'accès, qui lui appartiennent. Ce n'est pas un écran
        // d'administration : il n'y a rien à y voir pour qui n'organise pas,
        // d'où une règle conditionnée à l'existence du profil plutôt qu'à une
        // permission `screen.*`.
        if ($user->organizer !== null) {
            $rules[] = ['action' => 'read', 'subject' => 'organizer-profile'];
        }

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
