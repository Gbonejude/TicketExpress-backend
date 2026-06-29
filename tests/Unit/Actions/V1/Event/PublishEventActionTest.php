<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\V1\Event;

use App\Actions\V1\Event\PublishEventAction;
use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublishEventActionTest extends TestCase
{
    use RefreshDatabase;

    private PublishEventAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new PublishEventAction;
    }

    public function test_validates_event_has_ticket_types(): void
    {
        $event = Event::factory()->draft()->create();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Impossible de publier un événement sans types de tickets.');

        $this->action->execute(['event' => $event]);
    }

    public function test_publishes_event_with_ticket_types(): void
    {
        $event = Event::factory()->draft()->create();
        TicketType::factory()->for($event)->create();

        $publishedEvent = $this->action->execute(['event' => $event]);

        $this->assertEquals(EventStatus::PUBLISHED, $publishedEvent->status);
    }

    public function test_prevents_republishing_published_event(): void
    {
        $event = Event::factory()->create([
            'status' => EventStatus::PUBLISHED,
        ]);
        TicketType::factory()->for($event)->create();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('L\'événement est déjà publié.');

        $this->action->execute(['event' => $event]);
    }

    public function test_publishes_event_with_multiple_ticket_types(): void
    {
        $event = Event::factory()->draft()->create();
        TicketType::factory()->for($event)->count(3)->create();

        $publishedEvent = $this->action->execute(['event' => $event]);

        $this->assertEquals(EventStatus::PUBLISHED, $publishedEvent->status);
        $this->assertEquals(3, $publishedEvent->ticketTypes()->count());
    }

    public function test_returns_fresh_event_instance(): void
    {
        $event = Event::factory()->draft()->create();
        TicketType::factory()->for($event)->create();

        $publishedEvent = $this->action->execute(['event' => $event]);

        $this->assertNotSame($event, $publishedEvent);
        $this->assertEquals($event->id, $publishedEvent->id);
    }
}
