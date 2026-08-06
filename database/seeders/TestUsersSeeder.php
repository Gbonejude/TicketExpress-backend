<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\OrganizerStatus;
use App\Enums\UserRole;
use App\Models\Organizer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('Password123!');

        // 1. Super Admin
        $superAdmin = User::where('email', 'superadmin@test.tg')->first();

        if (! $superAdmin) {
            $superAdmin = User::create([
                'email' => 'superadmin@test.tg',
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'phone' => '+22890999901',
                'password' => $password,
                'email_verified_at' => now(),
            ]);
        }

        if (! $superAdmin->hasRole('super-admin')) {
            $superAdmin->assignRole('super-admin');
        }

        // 2. Admin
        $admin = User::where('email', 'admin@test.tg')->first();

        if (! $admin) {
            $admin = User::create([
                'email' => 'admin@test.tg',
                'first_name' => 'Admin',
                'last_name' => 'Test',
                'phone' => '+22890999902',
                'password' => $password,
                'email_verified_at' => now(),
            ]);
        }

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // 3. Participant de Test (Komi CREPPY)
        $client = User::where('email', 'judasgbone@gmail.com')->first();

        if (! $client) {
            $client = User::create([
                'email' => 'judasgbone@gmail.com',
                'first_name' => 'Komi',
                'last_name' => 'CREPPY',
                'phone' => '+22890510465',
                'password' => $password,
                'email_verified_at' => now(),
            ]);
        }

        if (! $client->hasRole(UserRole::PARTICIPANT->value)) {
            $client->assignRole(UserRole::PARTICIPANT->value);
        }

        // 4. Organizer Manager de Test
        $organizerManager = User::where('email', 'organizer@test.tg')->first();

        if (! $organizerManager) {
            $organizerManager = User::create([
                'email' => 'organizer@test.tg',
                'first_name' => 'Organizer',
                'last_name' => 'Manager',
                'phone' => '+22890999903',
                'password' => $password,
                'email_verified_at' => now(),
            ]);
        }

        if (! $organizerManager->hasRole('organizer-manager')) {
            $organizerManager->assignRole('organizer-manager');
        }

        // Create and validate organizer for the organizer-manager
        if (! $organizerManager->organizer) {
            Organizer::create([
                'user_id' => $organizerManager->id,
                'company_name' => 'Test Event Company',
                'description' => 'A test organizer company for API testing',
                'website' => 'https://test-organizer.tg',
                'status' => OrganizerStatus::APPROVED,
            ]);
        } else {
            // Update existing organizer to approved status
            $organizerManager->organizer->update([
                'status' => OrganizerStatus::APPROVED,
            ]);
        }

        $this->command->info('Test users seeded successfully!');
        $this->command->info('- Super Admin: superadmin@test.tg');
        $this->command->info('- Admin: admin@test.tg');
        $this->command->info('- Participant (Komi CREPPY): judasgbone@gmail.com');
        $this->command->info('- Organizer Manager: organizer@test.tg');
        $this->command->info('Password for all: Password123!');
    }
}
