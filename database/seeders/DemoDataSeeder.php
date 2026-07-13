<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\OrganizerStatus;
use App\Enums\TicketStatus;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventOccurrence;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Organizer;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Ticket;
use App\Models\TicketDownloadLink;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Venue;
use App\Models\Withdrawal;
use App\Notifications\CustomNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Fictional data so the back-office lists are populated for demos:
 * organizers, physical & online events with occurrences, ticket types (some
 * with promotions), orders, payments, issued/checked-in tickets, download
 * links, reviews, coupons, withdrawals and notifications.
 *
 * Idempotent: re-running does nothing once the main organizer has events.
 */
final class DemoDataSeeder extends Seeder
{
    /** @var array<int, string> */
    private array $usedOrderNumbers = [];

    /**
     * A unique random 7-digit order number (matches Order::generateOrderNumber
     * and the orders.order_number column).
     */
    private function nextOrderNumber(): string
    {
        do {
            $number = (string) random_int(1000000, 9999999);
        } while (in_array($number, $this->usedOrderNumbers, true));

        $this->usedOrderNumbers[] = $number;

        return $number;
    }

    public function run(): void
    {
        if (EventCategory::count() === 0) {
            EventCategory::factory()->count(6)->create();
        }
        if (Venue::count() === 0) {
            Venue::factory()->count(5)->create();
        }

        $categories = EventCategory::all();
        $venues = Venue::all();

        $orgUser = User::where('email', 'organizer@test.tg')->first();
        if ($orgUser === null) {
            $this->command->warn('organizer@test.tg missing — run TestUsersSeeder first.');

            return;
        }

        /** @var Organizer $organizer */
        $organizer = Organizer::firstOrCreate(
            ['user_id' => $orgUser->id],
            [
                'company_name' => 'Événements Lomé',
                'description' => 'Organisateur de référence à Lomé pour concerts, festivals et conférences.',
                'website' => 'https://evenements-lome.tg',
                'status' => OrganizerStatus::APPROVED,
                'is_active' => true,
            ],
        );

        if ($organizer->events()->exists()) {
            $this->command->info('Demo data already present — skipping.');

            return;
        }

        // A few extra organizers so the organizers screen has variety.
        Organizer::factory()->count(3)->create();
        Organizer::factory()->pending()->count(2)->create();

        $client = User::where('email', 'judasgbone@gmail.com')->first() ?? User::factory()->create();

        for ($i = 0; $i < 6; $i++) {
            $isOnline = $i % 2 === 1;

            /** @var Event $event */
            $event = Event::factory()->create([
                'organizer_id' => $organizer->id,
                'category_id' => $categories->random()->id,
                'venue_id' => $isOnline ? null : $venues->random()->id,
                'event_type' => $isOnline ? EventType::ONLINE : EventType::PHYSICAL,
                'online_url' => $isOnline ? 'https://live.ticketexpress.tg/'.Str::lower(Str::random(8)) : null,
                'status' => EventStatus::PUBLISHED,
                'refund_allowed' => true,
                'refund_days_before' => random_int(2, 14),
            ]);

            $ticketTypes = TicketType::factory()->count(random_int(2, 3))->create([
                'event_id' => $event->id,
                'occurrence_id' => null,
            ]);

            if ($isOnline) {
                $occurrences = EventOccurrence::factory()->count(2)->create(['event_id' => $event->id]);
                // Tie the first ticket type to the first occurrence to demo
                // per-date pricing on multi-date events.
                $ticketTypes->first()->update(['occurrence_id' => $occurrences->first()->id]);
            }

            // Promotion (-20%) on the first ticket type of every other event.
            if ($i % 2 === 0) {
                $tt = $ticketTypes->first();
                $tt->update([
                    'promotional_price' => round(((float) $tt->price) * 0.8),
                    'promotion_start_date' => now()->subDay(),
                    'promotion_end_date' => now()->addMonth(),
                ]);
            }

            Review::factory()->count(random_int(1, 4))->create([
                'event_id' => $event->id,
                'user_id' => $client->id,
                'comment' => fake()->sentence(12),
            ]);

            if ($i < 4) {
                foreach (range(1, random_int(2, 4)) as $ignored) {
                    $this->createPaidOrder($ticketTypes, $client);
                }
                $this->createPendingOrder($ticketTypes, $client);
            }
        }

        // Favorites: a few participants favorite random events.
        $participants = User::factory()->count(6)->create()->push($client);
        foreach (Event::where('organizer_id', $organizer->id)->get() as $ev) {
            $favers = $participants->random(random_int(0, $participants->count()));
            $ev->favoritedBy()->syncWithoutDetaching($favers->pluck('id')->all());
        }

        Coupon::factory()->count(4)->create();
        Withdrawal::factory()->count(2)->create(['organizer_id' => $organizer->id]);

        $orgUser->notify(new CustomNotification('Bienvenue', 'Votre espace organisateur est actif.', 'success'));
        $orgUser->notify(new CustomNotification('Nouvelle vente', 'Un billet vient d\'être vendu pour votre événement.', 'info'));

        if ($admin = User::where('email', 'admin@test.tg')->first()) {
            $admin->notify(new CustomNotification('Organisateur en attente', 'Un nouvel organisateur attend votre validation.', 'warning'));
        }

        $this->command->info('✅ Demo data seeded.');
    }

    /**
     * @param  Collection<int, TicketType>  $ticketTypes
     */
    private function createPaidOrder(Collection $ticketTypes, User $client): void
    {
        /** @var Order $order */
        $order = Order::factory()->paid()->create([
            'user_id' => $client->id,
            'order_number' => $this->nextOrderNumber(),
            'first_name' => $client->first_name ?? 'Client',
            'last_name' => $client->last_name ?? 'Test',
            'email' => $client->email,
            'payment_method' => fake()->randomElement(['flooz', 'tmoney']),
            'paid_at' => now(),
        ]);

        $total = 0.0;

        foreach ($ticketTypes->take(2) as $tt) {
            $qty = random_int(1, 3);
            $unit = (float) $tt->price;

            OrderItem::create([
                'order_id' => $order->id,
                'ticket_type_id' => $tt->id,
                'quantity' => $qty,
                'unit_price' => $unit,
                'subtotal' => $unit * $qty,
            ]);
            $total += $unit * $qty;

            foreach (range(1, $qty) as $ignored) {
                $ticket = Ticket::factory()->create([
                    'order_id' => $order->id,
                    'ticket_type_id' => $tt->id,
                ]);

                if (random_int(0, 1) === 1) {
                    $ticket->update([
                        'status' => TicketStatus::USED,
                        'checked_in_at' => now()->subHours(random_int(1, 48)),
                    ]);
                }
            }
        }

        $order->update(['total_amount' => $total]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'amount' => $total,
            'method' => fake()->randomElement([PaymentMethod::FLOOZ, PaymentMethod::TMONEY]),
            'status' => PaymentStatus::PAYE,
            'paid_at' => now(),
        ]);

        TicketDownloadLink::create([
            'token' => Str::random(40),
            'order_id' => $order->id,
            'expires_at' => now()->addDays(30),
            'download_count' => random_int(0, 6),
            'max_downloads' => 50,
        ]);
    }

    /**
     * @param  Collection<int, TicketType>  $ticketTypes
     */
    private function createPendingOrder(Collection $ticketTypes, User $client): void
    {
        /** @var Order $order */
        $order = Order::factory()->pending()->create([
            'user_id' => $client->id,
            'order_number' => $this->nextOrderNumber(),
            'email' => $client->email,
            'payment_method' => 'flooz',
        ]);

        $tt = $ticketTypes->first();
        $qty = random_int(1, 2);
        $unit = (float) $tt->price;

        OrderItem::create([
            'order_id' => $order->id,
            'ticket_type_id' => $tt->id,
            'quantity' => $qty,
            'unit_price' => $unit,
            'subtotal' => $unit * $qty,
        ]);

        $order->update(['total_amount' => $unit * $qty]);

        Payment::factory()->pending()->create([
            'order_id' => $order->id,
            'amount' => $unit * $qty,
            'method' => PaymentMethod::FLOOZ,
        ]);
    }
}
