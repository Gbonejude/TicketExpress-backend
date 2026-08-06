<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WithdrawalStatus;
use App\Models\Organizer;
use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Withdrawal>
 */
final class WithdrawalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organizer_id' => Organizer::factory(),
            'requester_phone' => '+228'.$this->faker->numerify('########'),
            'amount' => $this->faker->randomFloat(2, 10000, 1000000),
            'status' => $this->faker->randomElement(WithdrawalStatus::cases()),
            'payment_method' => $this->faker->randomElement(['flooz', 'tmoney']),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WithdrawalStatus::PENDING,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WithdrawalStatus::COMPLETED,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WithdrawalStatus::CANCELLED,
        ]);
    }
}
