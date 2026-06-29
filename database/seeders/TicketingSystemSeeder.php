<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EventStatus;
use App\Enums\OrganizerStatus;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Organizer;
use App\Models\Review;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

final class TicketingSystemSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Starting TicketExpress System Seeding...');

        // 1. Create Roles
        $this->command->info('Creating roles...');
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $organizerRole = Role::firstOrCreate(['name' => 'organizer']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // 2. Create Admin User
        $this->command->info('Creating admin user...');
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'System',
            'email' => 'admin@ticketexpress.tg',
            'phone' => '+22890000001',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole($adminRole);
        $this->command->info('✅ Admin: admin@ticketexpress.tg / password');

        // 3. Create Categories
        $this->command->info('Creating event categories...');
        $this->call(EventCategorySeeder::class);

        // 4. Create Venues
        $this->command->info('Creating venues...');
        $this->call(VenueSeeder::class);

        // 5. Create Organizers with Users
        $this->command->info('Creating organizers...');
        $organizers = [];
        $organizerNames = [
            ['first' => 'Jean', 'last' => 'Mensah', 'company' => 'EventPro Togo'],
            ['first' => 'Marie', 'last' => 'Koffi', 'company' => 'African Events'],
            ['first' => 'Paul', 'last' => 'Agbodjan', 'company' => 'Lomé Entertainment'],
            ['first' => 'Sophie', 'last' => 'Ayité', 'company' => 'Culture & Arts Togo'],
        ];

        foreach ($organizerNames as $index => $org) {
            $user = User::create([
                'first_name' => $org['first'],
                'last_name' => $org['last'],
                'email' => strtolower($org['first']).'@'.str_replace(' ', '', strtolower($org['company'])).'.tg',
                'phone' => '+2289000000'.($index + 2),
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $user->assignRole($organizerRole);

            $organizer = Organizer::create([
                'user_id' => $user->id,
                'company_name' => $org['company'],
                'description' => 'Organisation professionnelle d\'événements au Togo et en Afrique de l\'Ouest.',
                'website' => 'https://www.'.str_replace(' ', '', strtolower($org['company'])).'.tg',
                'status' => OrganizerStatus::APPROVED,
            ]);
            $organizers[] = $organizer;

            $this->command->info('✅ Organizer: '.$user->email.' / password');
        }

        // 6. Create Regular Users
        $this->command->info('Creating regular users...');
        $users = [];
        for ($i = 0; $i < 10; $i++) {
            $user = User::factory()->create();
            $user->assignRole($userRole);
            $users[] = $user;
        }
        $this->command->info('✅ '.count($users).' users created');

        // 7. Create Events with Ticket Types
        $this->command->info('Creating events with ticket types...');
        $categories = EventCategory::all();
        $venues = Venue::all();
        $events = [];

        foreach ($organizers as $organizer) {
            // Each organizer creates 3-5 events
            $eventCount = rand(3, 5);

            for ($i = 0; $i < $eventCount; $i++) {
                $status = match (rand(1, 10)) {
                    1, 2 => EventStatus::DRAFT,
                    9 => EventStatus::FINISHED,
                    10 => EventStatus::CANCELLED,
                    default => EventStatus::PUBLISHED,
                };

                $event = Event::factory()
                    ->state(['status' => $status])
                    ->create([
                        'organizer_id' => $organizer->id,
                        'category_id' => $categories->random()->id,
                        'venue_id' => $venues->random()->id,
                    ]);

                // Create 2-4 ticket types per event
                $ticketCount = rand(2, 4);
                $ticketNames = ['VIP', 'Standard', 'Étudiant', 'Groupe', 'Early Bird'];

                for ($j = 0; $j < $ticketCount; $j++) {
                    TicketType::factory()
                        ->create([
                            'event_id' => $event->id,
                            'name' => $ticketNames[$j] ?? 'Catégorie '.($j + 1),
                            'price' => 5000 * ($ticketCount - $j + 1),
                        ]);
                }

                $events[] = $event;

                // Add 2-5 reviews for published/finished events
                if (in_array($status, [EventStatus::PUBLISHED, EventStatus::FINISHED])) {
                    $reviewCount = rand(2, 5);
                    for ($r = 0; $r < $reviewCount; $r++) {
                        Review::create([
                            'event_id' => $event->id,
                            'user_id' => $users[array_rand($users)]->id,
                            'rating' => rand(3, 5),
                            'comment' => 'Excellent événement! Je recommande vivement.',
                        ]);
                    }
                }
            }
        }

        $this->command->info('✅ '.count($events).' events created with ticket types');

        // 8. Create Coupons
        $this->command->info('Creating coupons...');
        $coupons = [
            ['code' => 'WELCOME20', 'type' => 'percent', 'value' => 20, 'max_usage' => 100],
            ['code' => 'SUMMER25', 'type' => 'percent', 'value' => 25, 'max_usage' => 50],
            ['code' => 'FIRST5K', 'type' => 'fixed', 'value' => 5000, 'max_usage' => 200],
            ['code' => 'FLASH30', 'type' => 'percent', 'value' => 30, 'max_usage' => 25],
            ['code' => 'STUDENT15', 'type' => 'percent', 'value' => 15, 'max_usage' => 500],
        ];

        foreach ($coupons as $couponData) {
            $coupon = Coupon::create([
                'code' => $couponData['code'],
                'type' => $couponData['type'],
                'value' => $couponData['value'],
                'max_usage' => $couponData['max_usage'],
                'used_count' => 0,
                'start_date' => now(),
                'end_date' => now()->addMonths(3),
            ]);

            // Attach to 2-3 random events
            $randomEvents = Event::where('status', EventStatus::PUBLISHED)
                ->inRandomOrder()
                ->limit(rand(2, 3))
                ->get();
            $coupon->events()->attach($randomEvents);
        }

        $this->command->info('✅ '.count($coupons).' coupons created');

        // Summary
        $this->command->info('');
        $this->command->info('🎉 ====================================');
        $this->command->info('🎉 TicketExpress System Seeding Complete!');
        $this->command->info('🎉 ====================================');
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('  - Categories: '.EventCategory::count());
        $this->command->info('  - Venues: '.Venue::count());
        $this->command->info('  - Organizers: '.Organizer::count());
        $this->command->info('  - Events: '.Event::count());
        $this->command->info('  - Ticket Types: '.TicketType::count());
        $this->command->info('  - Coupons: '.Coupon::count());
        $this->command->info('  - Users: '.User::count());
        $this->command->info('  - Reviews: '.Review::count());
        $this->command->info('');
        $this->command->info('🔑 Test Credentials:');
        $this->command->info('  Admin: admin@ticketexpress.tg / password');
        $this->command->info('  Organizer: jean@eventprotogo.tg / password');
        $this->command->info('');
        $this->command->info('🎫 Test Coupons:');
        foreach ($coupons as $coupon) {
            $this->command->info('  - '.$coupon['code'].' ('.$coupon['value'].($coupon['type'] === 'percent' ? '%' : ' FCFA').' off)');
        }
        $this->command->info('');
    }
}
