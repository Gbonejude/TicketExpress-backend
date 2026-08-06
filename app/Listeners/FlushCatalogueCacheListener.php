<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ResourceChangedEvent;
use App\Support\CatalogueCache;

/**
 * Clears the public catalogue cache whenever something it shows changes.
 *
 * Hooked onto `ResourceChangedEvent` rather than sprinkled through the
 * controllers: every write already dispatches that event, so a route added
 * later is covered without anyone remembering to invalidate. The trade is that
 * this listener has to decide what is catalogue-relevant, which is the list
 * below.
 *
 * Deliberately synchronous. Queued, the cache would still serve the old
 * catalogue until a worker picked the job up — and the queue is exactly what
 * tends not to be running on a small deployment.
 */
final class FlushCatalogueCacheListener
{
    /**
     * Resources a public list can show.
     *
     * Orders are here because paying for tickets moves `sold_quantity`, which
     * is what "dernières places" and the sold-out badge are computed from.
     *
     * @var list<string>
     */
    private const CATALOGUE_RESOURCES = [
        'events',
        'event-categories',
        'categories',
        'venues',
        'organizers',
        'ticket-types',
        'promotions',
        'orders',
    ];

    public function handle(ResourceChangedEvent $event): void
    {
        // `channelName` is the resource: the event names its broadcast channel
        // after the collection that changed ("events", "organizers", …).
        if (! in_array($event->channelName, self::CATALOGUE_RESOURCES, true)) {
            return;
        }

        CatalogueCache::flush();
    }
}
