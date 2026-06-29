<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CheckIn;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CheckIn>
 */
final class CheckInFactory extends Factory
{
    protected $model = CheckIn::class;

    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'scanned_by' => User::factory(),
            'scanned_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'device_info' => $this->faker->userAgent(),
        ];
    }
}
