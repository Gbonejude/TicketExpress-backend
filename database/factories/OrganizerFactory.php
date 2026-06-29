<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrganizerStatus;
use App\Models\Organizer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organizer>
 */
final class OrganizerFactory extends Factory
{
    protected $model = Organizer::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_name' => $this->faker->company().' Events',
            'description' => $this->faker->paragraph(3),
            'website' => $this->faker->url(),
            'status' => OrganizerStatus::APPROVED,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrganizerStatus::PENDING,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrganizerStatus::REJECTED,
        ]);
    }
}
