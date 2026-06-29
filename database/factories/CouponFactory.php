<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Coupon>
 */
final class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement([CouponType::PERCENT, CouponType::FIXED]);

        return [
            'code' => strtoupper(Str::random(8)),
            'type' => $type,
            'value' => $type === CouponType::PERCENT
                ? $this->faker->randomElement([10, 15, 20, 25, 30])
                : $this->faker->randomElement([5000, 10000, 15000, 20000]),
            'max_usage' => $this->faker->randomElement([10, 25, 50, 100, 500]),
            'used_count' => 0,
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
        ];
    }

    public function percent(int $value): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CouponType::PERCENT,
            'value' => $value,
        ]);
    }

    public function fixed(int $value): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CouponType::FIXED,
            'value' => $value,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => now()->subMonths(2),
            'end_date' => now()->subMonth(),
        ]);
    }
}
