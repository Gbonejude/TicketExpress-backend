<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
#[Group('promotions')]
#[Group('feature4')]
 */
final class TicketTypePromotionTest extends TestCase
{
    use RefreshDatabase;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->event = Event::factory()->create();
    }

    #[Test]
    public function it_returns_false_when_no_promotional_price_is_set(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'price' => 10000,
            'promotional_price' => null,
        ]);

        $this->assertFalse($ticketType->hasActivePromotion());
    }

    #[Test]
    public function it_returns_true_when_promotion_is_active(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'price' => 10000,
            'promotional_price' => 6000,
            'promotion_start_date' => now()->subDay(),
            'promotion_end_date' => now()->addDay(),
        ]);

        $this->assertTrue($ticketType->hasActivePromotion());
    }

    #[Test]
    public function it_returns_false_when_promotion_has_not_started_yet(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'price' => 10000,
            'promotional_price' => 6000,
            'promotion_start_date' => now()->addDay(),
            'promotion_end_date' => now()->addDays(2),
        ]);

        $this->assertFalse($ticketType->hasActivePromotion());
    }

    #[Test]
    public function it_returns_false_when_promotion_has_ended(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'price' => 10000,
            'promotional_price' => 6000,
            'promotion_start_date' => now()->subDays(2),
            'promotion_end_date' => now()->subDay(),
        ]);

        $this->assertFalse($ticketType->hasActivePromotion());
    }

    #[Test]
    public function it_returns_promotional_price_when_promotion_is_active(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'price' => 10000,
            'promotional_price' => 6000,
            'promotion_start_date' => now()->subDay(),
            'promotion_end_date' => now()->addDay(),
        ]);

        $this->assertEquals(6000.0, $ticketType->currentPrice());
    }

    #[Test]
    public function it_returns_regular_price_when_no_promotion_is_active(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'price' => 10000,
            'promotional_price' => null,
        ]);

        $this->assertEquals(10000.0, $ticketType->currentPrice());
    }

    #[Test]
    public function it_calculates_discount_percentage_correctly(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'price' => 10000,
            'promotional_price' => 6000,
            'promotion_start_date' => now()->subDay(),
            'promotion_end_date' => now()->addDay(),
        ]);

        $this->assertEquals(40.0, $ticketType->discountPercentage());
    }

    #[Test]
    public function it_returns_null_discount_percentage_when_no_promotion(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'price' => 10000,
            'promotional_price' => null,
        ]);

        $this->assertNull($ticketType->discountPercentage());
    }

    #[Test]
    public function it_returns_null_discount_percentage_when_promotion_ended(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'price' => 10000,
            'promotional_price' => 6000,
            'promotion_start_date' => now()->subDays(2),
            'promotion_end_date' => now()->subDay(),
        ]);

        $this->assertNull($ticketType->discountPercentage());
    }

    #[Test]
    public function it_calculates_50_percent_discount_correctly(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'price' => 20000,
            'promotional_price' => 10000,
            'promotion_start_date' => now()->subDay(),
            'promotion_end_date' => now()->addDay(),
        ]);

        $this->assertEquals(50.0, $ticketType->discountPercentage());
    }

    #[Test]
    public function it_calculates_25_percent_discount_correctly(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'price' => 8000,
            'promotional_price' => 6000,
            'promotion_start_date' => now()->subDay(),
            'promotion_end_date' => now()->addDay(),
        ]);

        $this->assertEquals(25.0, $ticketType->discountPercentage());
    }

    #[Test]
    public function it_returns_null_discount_percentage_when_price_is_zero(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'price' => 0,
            'promotional_price' => 0,
            'promotion_start_date' => now()->subDay(),
            'promotion_end_date' => now()->addDay(),
        ]);

        $this->assertNull($ticketType->discountPercentage());
    }

    #[Test]
    public function it_handles_promotion_without_end_date(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'price' => 10000,
            'promotional_price' => 7000,
            'promotion_start_date' => now()->subDay(),
            'promotion_end_date' => null,
        ]);

        $this->assertTrue($ticketType->hasActivePromotion());
        $this->assertEquals(7000.0, $ticketType->currentPrice());
    }

    #[Test]
    public function it_handles_promotion_without_start_date(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'price' => 10000,
            'promotional_price' => 7000,
            'promotion_start_date' => null,
            'promotion_end_date' => now()->addDay(),
        ]);

        $this->assertTrue($ticketType->hasActivePromotion());
        $this->assertEquals(7000.0, $ticketType->currentPrice());
    }

    #[Test]
    public function it_rounds_discount_percentage_to_zero_decimals(): void
    {
        $ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'price' => 7500,
            'promotional_price' => 5000,
            'promotion_start_date' => now()->subDay(),
            'promotion_end_date' => now()->addDay(),
        ]);

        // (7500 - 5000) / 7500 * 100 = 33.333... → should be rounded to 33
        $this->assertEquals(33.0, $ticketType->discountPercentage());
    }
}
