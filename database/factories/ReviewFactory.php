<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
final class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->optional(0.8)->paragraph(),
        ];
    }

    public function excellent(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 5,
            'comment' => $this->faker->randomElement([
                'Événement excellent ! Je recommande vivement.',
                'Une expérience inoubliable, tout était parfait.',
                'Organisation impeccable, j\'ai adoré !',
            ]),
        ]);
    }

    public function poor(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => $this->faker->numberBetween(1, 2),
            'comment' => $this->faker->randomElement([
                'Décevant, ne correspond pas à mes attentes.',
                'Organisation à revoir, plusieurs problèmes.',
                'Pas satisfait de l\'expérience.',
            ]),
        ]);
    }
}
