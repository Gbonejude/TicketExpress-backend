<?php

declare(strict_types=1);

namespace App\Support;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Caching for the public catalogue: the event list and the category list.
 *
 * Those two are the hottest read paths on the site — every visitor hits them
 * before doing anything else — and they are expensive to build: filtering,
 * sorting on a correlated sub-query, and eager-loading four relations.
 *
 * **Invalidation is by version, not by tags.** The configured cache store is
 * `database`, which does not support tagging, so every key carries a version
 * number and {@see flush()} simply increments it. The old entries are then
 * unreachable and expire on their own. That is coarse — one publish clears the
 * whole catalogue — which is the right trade here: writes are rare, reads are
 * constant, and a stale price on a card is worse than a recomputed query.
 */
final class CatalogueCache
{
    private const VERSION_KEY = 'catalogue:version';

    /** Events change as tickets sell, so their list is only briefly cached. */
    public const EVENTS_TTL = 60;

    /** Categories change when an administrator adds one, and not otherwise. */
    public const CATEGORIES_TTL = 3600;

    /**
     * Runs `$callback` unless its result is already cached.
     *
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public static function remember(string $prefix, Request $request, int $ttl, Closure $callback): mixed
    {
        return Cache::remember(self::key($prefix, $request), $ttl, $callback);
    }

    /**
     * Key for this exact request.
     *
     * The whole query string is part of it — a filtered list is a different
     * result — and so is whether the caller is authenticated: `GET /events`
     * hides deactivated organizers from anonymous visitors only, and serving a
     * back-office user's cached page to the public would leak those events.
     */
    private static function key(string $prefix, Request $request): string
    {
        $query = $request->query();

        // Sorted, so `?a=1&b=2` and `?b=2&a=1` share one entry.
        ksort($query);

        return sprintf(
            'catalogue:v%d:%s:%s:%s',
            self::version(),
            $prefix,
            $request->user() === null ? 'guest' : 'auth',
            md5(json_encode($query, JSON_THROW_ON_ERROR)),
        );
    }

    private static function version(): int
    {
        return (int) Cache::rememberForever(self::VERSION_KEY, static fn (): int => 1);
    }

    /**
     * Invalidates the whole catalogue.
     *
     * Call after any write that a public list could show: an event, a ticket
     * type, a category, a venue or an organizer.
     */
    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }
}
