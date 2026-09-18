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
        $superAdmin = User::where('email', 'superadmin@gmail.com')->first()
            ?? User::where('email', 'superadmin@test.com')->first()
            ?? User::where('email', 'superadmin@test.tg')->first();

        if (! $superAdmin) {
            $superAdmin = User::create([
                'email' => 'superadmin@gmail.com',
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'phone' => '+22890999901',
                'password' => $password,
                'email_verified_at' => now(),
            ]);
        } else {
            $superAdmin->update(['email' => 'superadmin@gmail.com']);
        }

        if (! $superAdmin->hasRole('super-admin')) {
            $superAdmin->assignRole('super-admin');
        }

        // 2. Admin
        $admin = User::where('email', 'admin@gmail.com')->first()
            ?? User::where('email', 'admin@test.com')->first()
            ?? User::where('email', 'admin@test.tg')->first();

        if (! $admin) {
            $admin = User::create([
                'email' => 'admin@gmail.com',
                'first_name' => 'Admin',
                'last_name' => 'Test',
                'phone' => '+22890999902',
                'password' => $password,
                'email_verified_at' => now(),
            ]);
        } else {
            $admin->update(['email' => 'admin@gmail.com']);
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
        $organizerManager = User::where('email', 'organizer@gmail.com')->first()
            ?? User::where('email', 'organizer@test.com')->first()
            ?? User::where('email', 'organizer@test.tg')->first();

        if (! $organizerManager) {
            $organizerManager = User::create([
                'email' => 'organizer@gmail.com',
                'first_name' => 'Organizer',
                'last_name' => 'Manager',
                'phone' => '+22890999903',
                'password' => $password,
                'email_verified_at' => now(),
            ]);
        } else {
            $organizerManager->update(['email' => 'organizer@gmail.com']);
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
                'website' => 'https://test-organizer.com',
                'status' => OrganizerStatus::APPROVED,
            ]);
        } else {
            // Update existing organizer to approved status
            $organizerManager->organizer->update([
                'status' => OrganizerStatus::APPROVED,
            ]);
        }

        $this->command->info('Test users seeded successfully!');
        $this->command->info('- Super Admin: superadmin@gmail.com');
        $this->command->info('- Admin: admin@gmail.com');
        $this->command->info('- Participant (Komi CREPPY): judasgbone@gmail.com');
        $this->command->info('- Organizer Manager: organizer@gmail.com');
        $this->command->info('Password for all: Password123!');
    }
}
