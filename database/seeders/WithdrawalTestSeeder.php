<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\WithdrawalStatus;
use App\Models\Organizer;
use App\Models\Withdrawal;
use Illuminate\Database\Seeder;

final class WithdrawalTestSeeder extends Seeder
{
    /**
     * Seed test withdrawals.
     */
    public function run(): void
    {
        $this->command->info('💰 Seeding test withdrawals...');

        // Get an approved organizer
        $organizer = Organizer::where('status', 'approved')->first();

        if (! $organizer) {
            // Try to get any organizer
            $organizer = Organizer::first();

            if (! $organizer) {
                $this->command->error('❌ No organizer found.');

                return;
            }
        }

        // Create 3 test withdrawals with different statuses
        $withdrawals = [
            [
                'status' => WithdrawalStatus::PENDING,
                'amount' => 50000,
                'payment_method' => 'flooz',
            ],
            [
                'status' => WithdrawalStatus::APPROVED,
                'amount' => 75000,
                'payment_method' => 'tmoney',
            ],
            [
                'status' => WithdrawalStatus::PAID,
                'amount' => 100000,
                'payment_method' => 'flooz',
            ],
        ];

        foreach ($withdrawals as $data) {
            $withdrawal = Withdrawal::create([
                'organizer_id' => $organizer->id,
                'amount' => $data['amount'],
                'status' => $data['status'],
                'payment_method' => $data['payment_method'],
            ]);

            $this->command->info("✅ Withdrawal {$withdrawal->id} created ({$data['status']->value}, {$data['amount']} FCFA)");
        }

        $this->command->info('🎉 Successfully seeded 3 test withdrawals');
    }
}
