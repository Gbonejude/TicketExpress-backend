<?php
use App\Models\Event;
use App\Models\Organizer;
use App\Models\TicketType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('debug price', function (): void {
    $org = Organizer::factory()->create(['is_active' => true]);
    $cheap = Event::factory()->for($org)->create(['title' => 'Abordable']);
    $pricey = Event::factory()->for($org)->create(['title' => 'Premium']);

    TicketType::factory()->for($cheap, 'event')->create(['price' => 5000, 'promotional_price' => null]);
    TicketType::factory()->for($pricey, 'event')->create(['price' => 80000, 'promotional_price' => null]);

    dump('driver: '.DB::connection()->getDriverName());
    dump(DB::select('select event_id, price, typeof(price) as t, (CASE WHEN promotional_price IS NOT NULL THEN promotional_price ELSE price END) <= 10000 as ok from ticket_types'));

    $r = $this->getJson('/api/v1/events?max_price=10000');
    dump(collect($r->json('data'))->pluck('title')->all());
    expect(true)->toBeTrue();
});
