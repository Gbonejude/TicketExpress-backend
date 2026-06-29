<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\V1\Coupon;

use App\Actions\V1\Coupon\ValidateCouponAction;
use App\Models\Coupon;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ValidateCouponActionTest extends TestCase
{
    use RefreshDatabase;

    private ValidateCouponAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ValidateCouponAction;
    }

    public function test_validates_coupon_existence(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Le code promo n\'existe pas.');

        $this->action->execute(['code' => 'NONEXISTENT']);
    }

    public function test_validates_coupon_usage_limits(): void
    {
        $coupon = Coupon::factory()->create([
            'code' => 'LIMITED',
            'max_usage' => 10,
            'used_count' => 10,
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Ce code promo a atteint sa limite d\'utilisation.');

        $this->action->execute(['code' => 'LIMITED']);
    }

    public function test_validates_coupon_date_range(): void
    {
        $coupon = Coupon::factory()->expired()->create([
            'code' => 'EXPIRED',
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Ce code promo n\'est pas valide pour cette période.');

        $this->action->execute(['code' => 'EXPIRED']);
    }

    public function test_validates_coupon_future_start_date(): void
    {
        $coupon = Coupon::factory()->create([
            'code' => 'FUTURE',
            'start_date' => now()->addWeek(),
            'end_date' => now()->addMonth(),
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Ce code promo n\'est pas valide pour cette période.');

        $this->action->execute(['code' => 'FUTURE']);
    }

    public function test_validates_event_applicability(): void
    {
        $event1 = Event::factory()->create();
        $event2 = Event::factory()->create();

        $coupon = Coupon::factory()->create([
            'code' => 'EVENT1ONLY',
        ]);
        $coupon->events()->attach($event1->id);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Ce code promo ne s\'applique pas à cet événement.');

        $this->action->execute(['code' => 'EVENT1ONLY', 'event_id' => $event2->id]);
    }

    public function test_returns_valid_coupon_data(): void
    {
        $coupon = Coupon::factory()->percent(20)->create([
            'code' => 'VALID20',
            'max_usage' => 100,
            'used_count' => 5,
        ]);

        $result = $this->action->execute(['code' => 'VALID20']);

        $this->assertTrue($result['valid']);
        $this->assertEquals($coupon->id, $result['coupon']['id']);
        $this->assertEquals('VALID20', $result['coupon']['code']);
        $this->assertEquals('percent', $result['coupon']['type']);
        $this->assertEquals(20, $result['coupon']['value']);
    }

    public function test_allows_coupon_for_specific_event(): void
    {
        $event = Event::factory()->create();

        $coupon = Coupon::factory()->create([
            'code' => 'EVENTSPECIFIC',
        ]);
        $coupon->events()->attach($event->id);

        $result = $this->action->execute(['code' => 'EVENTSPECIFIC', 'event_id' => $event->id]);

        $this->assertTrue($result['valid']);
        $this->assertEquals($coupon->id, $result['coupon']['id']);
    }

    public function test_allows_global_coupon_for_any_event(): void
    {
        $event = Event::factory()->create();

        $coupon = Coupon::factory()->create([
            'code' => 'GLOBAL',
        ]);

        $result = $this->action->execute(['code' => 'GLOBAL', 'event_id' => $event->id]);

        $this->assertTrue($result['valid']);
    }

    public function test_validates_coupon_without_event(): void
    {
        $coupon = Coupon::factory()->create([
            'code' => 'ANYTIME',
            'max_usage' => 100,
            'used_count' => 5,
        ]);

        $result = $this->action->execute(['code' => 'ANYTIME']);

        $this->assertTrue($result['valid']);
        $this->assertEquals($coupon->id, $result['coupon']['id']);
    }
}
