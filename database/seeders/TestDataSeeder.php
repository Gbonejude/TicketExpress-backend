<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Organizer;
use App\Models\Review;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Venue;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Creating test data for TicketExpress platform...');

        DB::beginTransaction();

        try {
            // 1. Créer des utilisateurs de test
            $this->createUsers();

            // 2. Créer des données avec factories
            $this->createDataWithFactories();

            DB::commit();

            $this->command->info('✅ Test data created successfully!');
            $this->command->info('📊 Summary:');
            $this->command->info('   - Users: '.User::count());
            $this->command->info('   - Organizers: '.Organizer::count());
            $this->command->info('   - Event Categories: '.EventCategory::count());
            $this->command->info('   - Venues: '.Venue::count());
            $this->command->info('   - Events: '.Event::count());
            $this->command->info('   - Ticket Types: '.TicketType::count());
            $this->command->info('   - Coupons: '.Coupon::count());
            $this->command->info('   - Reviews: '.Review::count());
        } catch (Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Failed to create test data: '.$e->getMessage());

            throw $e;
        }
    }

    private function createUsers(): void
    {
        $this->command->info('Creating users...');

        $usersData = [
            [
                'user' => [
                    'last_name' => 'Admin',
                    'first_name' => 'Super',
                    'email' => 'admin@ticketexpress.com',
                    'phone' => '+22890000001',
                    'password' => Hash::make('password'),
                ],
                'role' => UserRole::SUPER_ADMIN->value,
            ],
            [
                'user' => [
                    'last_name' => 'Manager',
                    'first_name' => 'John',
                    'email' => 'manager@ticketexpress.com',
                    'phone' => '+22890000002',
                    'password' => Hash::make('password'),
                ],
                'role' => 'organizer-manager',
            ],
            [
                'user' => [
                    'last_name' => 'Doe',
                    'first_name' => 'Jane',
                    'email' => 'client1@example.com',
                    'phone' => '+22890000003',
                    'password' => Hash::make('password'),
                ],
                'role' => UserRole::CLIENT->value,
            ],
            [
                'user' => [
                    'last_name' => 'Smith',
                    'first_name' => 'Robert',
                    'email' => 'client2@example.com',
                    'phone' => '+22890000004',
                    'password' => Hash::make('password'),
                ],
                'role' => UserRole::CLIENT->value,
            ],
        ];

        foreach ($usersData as $data) {
            $user = User::firstOrCreate(
                ['phone' => $data['user']['phone']],
                $data['user']
            );

            if (! $user->hasRole($data['role'])) {
                $user->assignRole($data['role']);
            }
        }
    }

    private function createDataWithFactories(): void
    {
        $this->command->info('Creating data with factories...');

        // Create event categories (use factory if available)
        if (! EventCategory::exists()) {
            EventCategory::factory()->count(5)->create();
        }

        // Create venues (use factory if available)
        if (! Venue::exists()) {
            Venue::factory()->count(3)->create();
        }

        // Create organizers linked to managers
        $managers = User::role('organizer-manager')->get();
        if ($managers->isNotEmpty() && Organizer::count() < 3) {
            foreach ($managers->take(3) as $manager) {
                Organizer::factory()->create(['user_id' => $manager->id]);
            }
        }

        // Create events (use factory if available)
        if (Event::count() < 5) {
            Event::factory()->count(5)->create();
        }

        // Create ticket types for events
        $events = Event::all();
        foreach ($events as $event) {
            if ($event->ticketTypes()->count() === 0) {
                TicketType::factory()->count(3)->create(['event_id' => $event->id]);
            }
        }

        // Create some coupons
        if (Coupon::count() < 3) {
            Coupon::factory()->count(3)->create();
        }

        // Create some reviews
        $clients = User::role(UserRole::CLIENT->value)->get();
        if ($clients->isNotEmpty() && Review::count() < 5) {
            Review::factory()->count(5)->create([
                'user_id' => $clients->random()->id,
            ]);
        }
    }
}
