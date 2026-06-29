<?php

declare(strict_types=1);

namespace App\Enums;

enum Screen: string
{
    case DASHBOARD = 'dashboard';
    case USERS = 'users';
    case CUSTOMERS = 'customers';
    case DRIVERS = 'drivers';
    case INTERNAL_DRIVERS = 'internal_drivers';
    case ONLINE_DRIVERS = 'online_drivers';
    case ORGANIZERS = 'organizers';
    case EVENTS = 'events';
    case VENUES = 'venues';
    case CATEGORIES = 'categories';
    case COUPONS = 'coupons';
    case TICKETS = 'tickets';
    case BOOKINGS = 'bookings';
    case REVIEWS = 'reviews';
    case ADMINISTRATORS = 'administrators';

    public function label(): string
    {
        return match ($this) {
            self::DASHBOARD => 'Dashboard',
            self::USERS => 'Users',
            self::CUSTOMERS => 'Customers',
            self::DRIVERS => 'Drivers',
            self::INTERNAL_DRIVERS => 'Internal Drivers',
            self::ONLINE_DRIVERS => 'Online Drivers',
            self::ORGANIZERS => 'Organizers',
            self::EVENTS => 'Events',
            self::VENUES => 'Venues',
            self::CATEGORIES => 'Categories',
            self::COUPONS => 'Coupons',
            self::TICKETS => 'Tickets',
            self::BOOKINGS => 'Bookings',
            self::REVIEWS => 'Reviews',
            self::ADMINISTRATORS => 'Administrators',
        };
    }

    public function permission(): string
    {
        return 'screen.'.$this->value;
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
     * @return array<int, array{key: string, label: string, permission: string}>
     */
    public static function catalogue(): array
    {
        return array_map(fn (self $s) => [
            'key' => $s->value,
            'label' => $s->label(),
            'permission' => $s->permission(),
        ], self::cases());
    }

    /**
     * Default screens for admin role (excludes super-admin-only screens).
     *
     * @return array<int, self>
     */
    public static function defaultAdminScreens(): array
    {
        return array_filter(
            self::cases(),
            static fn (self $screen): bool => $screen !== self::ADMINISTRATORS,
        );
    }
}
