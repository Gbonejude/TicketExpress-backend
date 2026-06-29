<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
final class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'amount' => $this->faker->randomFloat(2, 5000, 100000),
            'method' => $this->faker->randomElement([PaymentMethod::MOBILE_MONEY, PaymentMethod::CASH, PaymentMethod::CARD]),
            'transaction_reference' => strtoupper($this->faker->bothify('TXN-####-????-####')),
            'status' => PaymentStatus::COMPLETED,
            'paid_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::PENDING,
            'paid_at' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::FAILED,
            'paid_at' => null,
        ]);
    }
}
