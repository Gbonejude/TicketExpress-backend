<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\Order\OrderCancelledEvent;
use App\Events\Order\OrderCreatedEvent;
use App\Events\Order\OrderPaidEvent;
use App\Events\Order\OrderRefundedEvent;
use App\Events\Organizer\OrganizerApprovedEvent;
use App\Events\Organizer\OrganizerRejectedEvent;
use App\Events\Organizer\OrganizerStatusUpdatedEvent;
use App\Events\ResourceChangedEvent;
use App\Events\Ticket\TicketCheckedInEvent;
use App\Listeners\FlushCatalogueCacheListener;
use App\Listeners\Order\OrderCancelledListener;
use App\Listeners\Order\OrderCreatedListener;
use App\Listeners\Order\OrderPaidListener;
use App\Listeners\Order\OrderRefundedListener;
use App\Listeners\Organizer\OrganizerApprovedListener;
use App\Listeners\Organizer\OrganizerRejectedListener;
use App\Listeners\Organizer\OrganizerStatusUpdatedListener;
use App\Listeners\Ticket\TicketCheckedInListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

final class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Order Events
        OrderCreatedEvent::class => [
            OrderCreatedListener::class,
        ],
        OrderPaidEvent::class => [
            OrderPaidListener::class,
        ],
        OrderCancelledEvent::class => [
            OrderCancelledListener::class,
        ],
        OrderRefundedEvent::class => [
            OrderRefundedListener::class,
        ],

        // Organizer Events
        OrganizerStatusUpdatedEvent::class => [
            OrganizerStatusUpdatedListener::class,
        ],
        // TODO: Create these listeners when needed
        // OrganizerApprovedEvent::class => [
        //     OrganizerApprovedListener::class,
        // ],
        // OrganizerRejectedEvent::class => [
        //     OrganizerRejectedListener::class,
        // ],

        // Any write the public catalogue could show invalidates its cache.
        ResourceChangedEvent::class => [
            FlushCatalogueCacheListener::class,
        ],

        // Ticket Events
        TicketCheckedInEvent::class => [
            TicketCheckedInListener::class,
        ],
    ];

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
