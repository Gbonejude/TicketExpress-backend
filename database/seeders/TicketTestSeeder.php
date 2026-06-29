<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\DeliveryMethod;
use App\Enums\OrderStatus;
use App\Events\Order\OrderPaidEvent;
use App\Models\Event;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

final class TicketTestSeeder extends Seeder
{
    /**
     * Seed test tickets by creating paid orders.
     */
    public function run(): void
    {
        $this->command->info('🎫 Seeding test tickets...');

        // Get test client user
        $client = User::where('email', 'judasgbone@gmail.com')->first();

        if (! $client) {
            $this->command->error('❌ Test client user not found. Run TestUsersSeeder first.');

            return;
        }

        // Get an active event with ticket types
        $event = Event::with('ticketTypes')
            ->whereHas('ticketTypes', function ($query): void {
                $query->where('quantity', '>', 0)
                    ->whereColumn('sold_quantity', '<', 'quantity');
            })
            ->first();

        if (! $event || $event->ticketTypes->isEmpty()) {
            $this->command->error('❌ No events with available ticket types found.');

            return;
        }

        $ticketType = $event->ticketTypes->first();

        // Create 3 test orders with tickets
        for ($i = 1; $i <= 3; $i++) {
            $order = Order::create([
                'user_id' => $client->id,
                'first_name' => 'Komi',
                'last_name' => 'CREPPY',
                'email' => 'judasgbone@gmail.com',
                'phone' => '+22890510465',
                'total_amount' => $ticketType->price * 2,
                'status' => OrderStatus::PAID,
                'payment_method' => 'mobile_money',
                'delivery_method' => DeliveryMethod::EMAIL,
            ]);

            // Create order items
            $order->items()->create([
                'ticket_type_id' => $ticketType->id,
                'quantity' => 2,
                'unit_price' => $ticketType->price,
                'subtotal' => $ticketType->price * 2,
            ]);

            // Dispatch OrderPaidEvent to generate tickets automatically
            event(new OrderPaidEvent($order));

            $this->command->info("✅ Order #{$order->order_number} created with 2 tickets");
        }

        // Count total tickets created
        $totalTickets = Order::whereNotNull('paid_at')->withCount('tickets')->get()->sum('tickets_count');

        $this->command->info("🎉 Successfully seeded {$totalTickets} test tickets across 3 orders");
    }
}
