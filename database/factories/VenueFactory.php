<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Venue>
 */
final class VenueFactory extends Factory
{
    protected $model = Venue::class;

    public function definition(): array
    {
        $venues = [
            ['name' => 'Palais des Congrès de Lomé', 'city' => 'Lomé', 'capacity' => 2000, 'lat' => 6.1319, 'lng' => 1.2228],
            ['name' => 'Stade de Kégué', 'city' => 'Lomé', 'capacity' => 5000, 'lat' => 6.1667, 'lng' => 1.2167],
            ['name' => 'Hotel 2 Février', 'city' => 'Lomé', 'capacity' => 500, 'lat' => 6.1256, 'lng' => 1.2319],
            ['name' => 'Centre Culturel Français', 'city' => 'Lomé', 'capacity' => 300, 'lat' => 6.1372, 'lng' => 1.2128],
            ['name' => 'Hôtel Sarakawa', 'city' => 'Lomé', 'capacity' => 800, 'lat' => 6.1353, 'lng' => 1.2164],
            ['name' => 'Espace Tamtam', 'city' => 'Lomé', 'capacity' => 400, 'lat' => 6.1289, 'lng' => 1.2245],
        ];

        $venue = $this->faker->randomElement($venues);

        return [
            'name' => $venue['name'],
            'address' => $this->faker->streetAddress(),
            'city' => $venue['city'],
            'country' => 'Togo',
            'capacity' => $venue['capacity'],
            'latitude' => $venue['lat'],
            'longitude' => $venue['lng'],
        ];
    }
}
