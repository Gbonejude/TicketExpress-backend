<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventOccurrence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventOccurrence>
 */
final class EventOccurrenceFactory extends Factory
{
    protected $model = EventOccurrence::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('+1 week', '+3 months');
        $endDate = (clone $startDate)->modify('+'.$this->faker->numberBetween(2, 8).' hours');

        return [
            'event_id' => Event::factory(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'max_attendees' => $this->faker->randomElement([null, 100, 500, 1000, 5000]),
            'current_attendees' => 0,
            'status' => 'active',
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function soldOut(): static
    {
        return $this->state(function (array $attributes) {
            $maxAttendees = $attributes['max_attendees'] ?? 100;

            return [
                'status' => 'sold_out',
                'current_attendees' => $maxAttendees,
            ];
        });
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    public function unlimited(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_attendees' => null,
        ]);
    }

    public function withCapacity(int $capacity): static
    {
        return $this->state(fn (array $attributes) => [
            'max_attendees' => $capacity,
            'current_attendees' => 0,
        ]);
    }
}
