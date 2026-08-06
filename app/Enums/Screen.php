<?php

declare(strict_types=1);

namespace App\Enums;

enum Screen: string
{
    case DASHBOARD = 'dashboard';
    case EVENTS = 'events';
    case BOOKINGS = 'bookings';
    case TICKETS = 'tickets';
    case PROMOTIONS = 'promotions';
    case PAYMENTS = 'payments';
    case COUPONS = 'coupons';
    case ORGANIZERS = 'organizers';
    case VENUES = 'venues';
    case CATEGORIES = 'categories';
    case WITHDRAWALS = 'withdrawals';
    case USERS = 'users';
    case NOTIFICATIONS = 'notifications';
    case ADMINISTRATORS = 'administrators';

    public function label(): string
    {
        return match ($this) {
            self::DASHBOARD => 'Tableau de bord',
            self::EVENTS => 'Événements',
            self::BOOKINGS => 'Commandes',
            self::TICKETS => 'Billetterie',
            self::PROMOTIONS => 'Promotions',
            self::PAYMENTS => 'Paiements',
            self::COUPONS => 'Coupons',
            self::ORGANIZERS => 'Organisateurs',
            self::VENUES => 'Lieux',
            self::CATEGORIES => 'Catégories',
            self::WITHDRAWALS => 'Retraits',
            self::USERS => 'Utilisateurs',
            self::NOTIFICATIONS => 'Notifications',
            self::ADMINISTRATORS => 'Administrateurs',
        };
    }

    public function permission(): string
    {
        return 'screen.'.$this->value;
    }

    /**
     * The CRUD actions that make sense for this screen (beyond simply viewing
     * it). Read-only screens return an empty list.
     *
     * @return array<int, string>
     */
    public function actions(): array
    {
        return match ($this) {
            self::EVENTS,
            self::TICKETS,
            self::PROMOTIONS,
            self::COUPONS,
            self::VENUES,
            self::CATEGORIES,
            self::USERS,
            self::ADMINISTRATORS => ['create', 'update', 'delete'],
            self::ORGANIZERS => ['update', 'delete'],
            default => [],
        };
    }

    /**
     * Permission names for this screen's actions (e.g. `events.create`).
     *
     * @return array<int, string>
     */
    public function actionPermissions(): array
    {
        return array_map(fn (string $a): string => $this->value.'.'.$a, $this->actions());
    }

    /**
     * Every action permission across all screens.
     *
     * @return array<int, string>
     */
    public static function allActionPermissions(): array
    {
        return array_merge(...array_map(
            static fn (self $s): array => $s->actionPermissions(),
            self::cases(),
        ));
    }

    /**
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return array_map(fn (self $s) => $s->permission(), self::cases());
    }

    /**
     * @return array<int, array{key: string, label: string, permission: string, actions: array<int, string>}>
     */
    public static function catalogue(): array
    {
        return array_map(fn (self $s) => [
            'key' => $s->value,
            'label' => $s->label(),
            'permission' => $s->permission(),
            'actions' => $s->actions(),
        ], self::cases());
    }

    /**
     * Default screens for admin role (excludes super-admin-only screens).
     *
     * @return array<int, self>
     */
    public static function defaultAdminScreens(): array
    {
        return array_values(array_filter(
            self::cases(),
            static fn (self $screen): bool => $screen !== self::ADMINISTRATORS,
        ));
    }

    /**
     * Default screens for the organizer-manager role. An organizer only sees
     * the screens tied to running their own events and box office.
     *
     * @return array<int, self>
     */
    public static function defaultOrganizerScreens(): array
    {
        return [
            self::DASHBOARD,
            self::EVENTS,
            self::TICKETS,
            self::PROMOTIONS,
            self::COUPONS,
            self::BOOKINGS,
            self::PAYMENTS,
            self::WITHDRAWALS,
        ];
    }
}
