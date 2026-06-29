<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Service responsible for cache invalidation across the application.
 */
final class CacheInvalidationService
{
    /**
     * Invalidate all event-related cache keys.
     */
    public function invalidateEvents(): void
    {
        try {
            Cache::tags(['events'])->flush();
            Log::info('Cache invalidated: events');
        } catch (\Throwable $e) {
            Log::error('Failed to invalidate events cache', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Invalidate cache for a specific event.
     */
    public function invalidateEvent(string $eventId): void
    {
        try {
            Cache::tags(['events', "event:{$eventId}"])->flush();
            Log::info('Cache invalidated: event', ['event_id' => $eventId]);
        } catch (\Throwable $e) {
            Log::error('Failed to invalidate event cache', [
                'event_id' => $eventId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Invalidate all order-related cache keys.
     */
    public function invalidateOrders(): void
    {
        try {
            Cache::tags(['orders'])->flush();
            Log::info('Cache invalidated: orders');
        } catch (\Throwable $e) {
            Log::error('Failed to invalidate orders cache', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Invalidate cache for a specific order.
     */
    public function invalidateOrder(string $orderId): void
    {
        try {
            Cache::tags(['orders', "order:{$orderId}"])->flush();
            Log::info('Cache invalidated: order', ['order_id' => $orderId]);
        } catch (\Throwable $e) {
            Log::error('Failed to invalidate order cache', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Invalidate cache for orders by user.
     */
    public function invalidateUserOrders(string $userId): void
    {
        try {
            Cache::tags(['orders', "user:{$userId}:orders"])->flush();
            Log::info('Cache invalidated: user orders', ['user_id' => $userId]);
        } catch (\Throwable $e) {
            Log::error('Failed to invalidate user orders cache', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Invalidate cache for orders by organizer.
     */
    public function invalidateOrganizerOrders(string $organizerId): void
    {
        try {
            Cache::tags(['orders', "organizer:{$organizerId}:orders"])->flush();
            Log::info('Cache invalidated: organizer orders', ['organizer_id' => $organizerId]);
        } catch (\Throwable $e) {
            Log::error('Failed to invalidate organizer orders cache', [
                'organizer_id' => $organizerId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Invalidate all ticket-related cache keys.
     */
    public function invalidateTickets(): void
    {
        try {
            Cache::tags(['tickets'])->flush();
            Log::info('Cache invalidated: tickets');
        } catch (\Throwable $e) {
            Log::error('Failed to invalidate tickets cache', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Invalidate cache for a specific ticket.
     */
    public function invalidateTicket(string $ticketId): void
    {
        try {
            Cache::tags(['tickets', "ticket:{$ticketId}"])->flush();
            Log::info('Cache invalidated: ticket', ['ticket_id' => $ticketId]);
        } catch (\Throwable $e) {
            Log::error('Failed to invalidate ticket cache', [
                'ticket_id' => $ticketId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Invalidate cache for tickets by user.
     */
    public function invalidateUserTickets(string $userId): void
    {
        try {
            Cache::tags(['tickets', "user:{$userId}:tickets"])->flush();
            Log::info('Cache invalidated: user tickets', ['user_id' => $userId]);
        } catch (\Throwable $e) {
            Log::error('Failed to invalidate user tickets cache', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Invalidate all organizer-related cache keys.
     */
    public function invalidateOrganizers(): void
    {
        try {
            Cache::tags(['organizers'])->flush();
            Log::info('Cache invalidated: organizers');
        } catch (\Throwable $e) {
            Log::error('Failed to invalidate organizers cache', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Invalidate cache for a specific organizer.
     */
    public function invalidateOrganizer(string $organizerId): void
    {
        try {
            Cache::tags(['organizers', "organizer:{$organizerId}"])->flush();
            Log::info('Cache invalidated: organizer', ['organizer_id' => $organizerId]);
        } catch (\Throwable $e) {
            Log::error('Failed to invalidate organizer cache', [
                'organizer_id' => $organizerId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
