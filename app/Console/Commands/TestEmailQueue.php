<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\DeliveryMethod;
use App\Enums\OrderStatus;
use App\Jobs\SendEmailJob;
use App\Mail\OrderConfirmationMail;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Organizer;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Console\Command;

final class TestEmailQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email-queue {--count=1 : Number of test emails to send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the email queue system by sending test emails';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $count = (int) $this->option('count');

        $this->info('Testing email queue system...');
        $this->info("Sending {$count} test email(s) to queue...");

        // Chercher une commande existante avec des items
        $existingOrder = Order::with(['items.ticketType'])->has('items')->first();

        if (! $existingOrder) {
            $this->warn('No existing orders with items found in database.');
            $this->info('Creating a test order with items...');

            try {
                // Créer une commande de test avec des items
                $existingOrder = $this->createTestOrder();
                $this->info("Test order created: #{$existingOrder->order_number}");
            } catch (\Exception $e) {
                $this->error('Failed to create test order: '.$e->getMessage());
                $this->line('');
                $this->warn('Please check your database and ensure all required tables exist.');

                return;
            }
        }

        $this->info("Using order: #{$existingOrder->order_number}");
        $this->info("Order has {$existingOrder->items->count()} item(s)");

        for ($i = 1; $i <= $count; $i++) {
            try {
                // Envoyer l'email via la file d'attente
                SendEmailJob::dispatch(
                    to: 'test@example.com',
                    mailableClass: OrderConfirmationMail::class,
                    mailableData: [$existingOrder],
                );

                $this->line("Email #{$i} dispatched to queue: Order #{$existingOrder->order_number}");
            } catch (\Exception $e) {
                $this->error("Failed to dispatch email #{$i}: ".$e->getMessage());
            }
        }

        $this->info("Done! {$count} email(s) have been dispatched to the queue.");
        $this->line('');
        $this->line('Next steps:');
        $this->line('1. Run: php artisan queue:work --queue=emails');
        $this->line('2. Or check queue: php artisan queue:monitor');
        $this->line('3. Check failed jobs: php artisan queue:failed');
        $this->line('4. Check jobs table: SELECT * FROM jobs;');
    }

    /**
     * Create a minimal test order with items for testing emails.
     */
    private function createTestOrder(): Order
    {
        // Utiliser une transaction pour s'assurer de la cohérence
        return \DB::transaction(function (): Order {
            // Créer ou récupérer un utilisateur de test
            $user = User::firstOrCreate(
                ['email' => 'test@example.com'],
                [
                    'first_name' => 'Test',
                    'last_name' => 'User',
                    'phone' => '+22890000000',
                    'password' => \Hash::make('password'),
                ]
            );

            // Récupérer un type de ticket existant ou en créer un
            $ticketType = TicketType::first();

            if (! $ticketType) {
                $this->warn('No ticket types found. Creating a minimal test ticket type...');

                // Récupérer ou créer les dépendances minimales
                $organizer = Organizer::first() ?? Organizer::factory()->create();
                $event = Event::first() ?? Event::factory()->create(['organizer_id' => $organizer->id]);
                $eventCategory = EventCategory::first() ?? EventCategory::factory()->create(['event_id' => $event->id]);

                $ticketType = TicketType::factory()->create([
                    'event_category_id' => $eventCategory->id,
                    'name' => 'Test Ticket',
                    'price' => 10000,
                    'total_quantity' => 100,
                    'available_quantity' => 100,
                ]);
            }

            // Créer la commande
            $order = Order::create([
                'user_id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'total_amount' => $ticketType->price * 2,
                'status' => OrderStatus::PAID,
                'payment_method' => 'card',
                'delivery_method' => DeliveryMethod::EMAIL,
            ]);

            // Créer les items de commande
            OrderItem::create([
                'order_id' => $order->id,
                'ticket_type_id' => $ticketType->id,
                'quantity' => 2,
                'unit_price' => $ticketType->price,
                'subtotal' => $ticketType->price * 2,
            ]);

            // Recharger la commande avec les relations
            return $order->load(['items.ticketType']);
        });
    }
}
