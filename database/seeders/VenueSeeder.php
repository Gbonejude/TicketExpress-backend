<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Venue;
use Illuminate\Database\Seeder;

final class VenueSeeder extends Seeder
{
    public function run(): void
    {
        $venues = [
            [
                'name' => 'Palais des Congrès de Lomé',
                'address' => 'Boulevard de la Marina',
                'city' => 'Lomé',
                'country' => 'Togo',
                'capacity' => 2000,
                'latitude' => 6.1319,
                'longitude' => 1.2228,
            ],
            [
                'name' => 'Stade de Kégué',
                'address' => 'Quartier Kégué',
                'city' => 'Lomé',
                'country' => 'Togo',
                'capacity' => 5000,
                'latitude' => 6.1667,
                'longitude' => 1.2167,
            ],
            [
                'name' => 'Hotel 2 Février',
                'address' => 'Rue de l\'UEMOA',
                'city' => 'Lomé',
                'country' => 'Togo',
                'capacity' => 500,
                'latitude' => 6.1256,
                'longitude' => 1.2319,
            ],
            [
                'name' => 'Centre Culturel Français',
                'address' => 'Avenue de la Présidence',
                'city' => 'Lomé',
                'country' => 'Togo',
                'capacity' => 300,
                'latitude' => 6.1372,
                'longitude' => 1.2128,
            ],
            [
                'name' => 'Hôtel Sarakawa',
                'address' => 'Boulevard du 13 Janvier',
                'city' => 'Lomé',
                'country' => 'Togo',
                'capacity' => 800,
                'latitude' => 6.1353,
                'longitude' => 1.2164,
            ],
            [
                'name' => 'Espace Tamtam',
                'address' => 'Quartier Tokoin',
                'city' => 'Lomé',
                'country' => 'Togo',
                'capacity' => 400,
                'latitude' => 6.1289,
                'longitude' => 1.2245,
            ],
            [
                'name' => 'Stade de Kara',
                'address' => 'Centre-ville',
                'city' => 'Kara',
                'country' => 'Togo',
                'capacity' => 3000,
                'latitude' => 9.5518,
                'longitude' => 1.1861,
            ],
            [
                'name' => 'Place de l\'Indépendance',
                'address' => 'Centre-ville',
                'city' => 'Lomé',
                'country' => 'Togo',
                'capacity' => 10000,
                'latitude' => 6.1278,
                'longitude' => 1.2219,
            ],
        ];

        foreach ($venues as $venue) {
            Venue::create($venue);
        }

        $this->command->info('✅ '.count($venues).' venues created!');
    }
}
