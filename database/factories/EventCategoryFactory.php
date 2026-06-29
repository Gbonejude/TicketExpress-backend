<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EventCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EventCategory>
 */
final class EventCategoryFactory extends Factory
{
    protected $model = EventCategory::class;

    public function definition(): array
    {
        $name = $this->faker->randomElement([
            'Concert',
            'Conférence',
            'Festival',
            'Sport',
            'Théâtre',
            'Cinéma',
            'Exposition',
            'Networking',
            'Formation',
            'Gala',
            'Spectacle',
            'Comédie',
        ]);

        // Generate unique slug by appending random suffix to avoid constraint violations
        $baseSlug = Str::slug($name);
        $uniqueSuffix = Str::random(6);

        return [
            'name' => $name,
            'slug' => $baseSlug.'-'.$uniqueSuffix,
        ];
    }
}
