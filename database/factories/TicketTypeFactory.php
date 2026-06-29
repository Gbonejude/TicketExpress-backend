<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketType>
 */
final class TicketTypeFactory extends Factory
{
    protected $model = TicketType::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(50, 500);
        $soldQuantity = $this->faker->numberBetween(0, (int) ($quantity * 0.7));

        $ticketTypes = [
            [
                'name' => 'VIP',
                'benefits' => ['🍾 Boissons offertes', '🍽️ Buffet inclus', '🚗 Parking VIP'],
                'location' => 'Loges VIP avec vue directe',
            ],
            [
                'name' => 'Standard',
                'benefits' => ['🎫 Entrée générale', '🚻 Accès toilettes'],
                'location' => 'Places assises standards',
            ],
            [
                'name' => 'Étudiant',
                'benefits' => ['🎓 Tarif réduit', '🎫 Entrée générale'],
                'location' => 'Places assises section étudiants',
            ],
            [
                'name' => 'Groupe',
                'benefits' => ['👥 Tarif de groupe', '🎫 Entrée générale'],
                'location' => 'Places regroupées',
            ],
            [
                'name' => 'Premium',
                'benefits' => ['✨ Places premium', '🍷 Boisson de bienvenue'],
                'location' => 'Section premium centrale',
            ],
        ];

        $type = $this->faker->randomElement($ticketTypes);

        return [
            'event_id' => Event::factory(),
            'name' => $type['name'],
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomElement([5000, 10000, 15000, 20000, 25000, 30000, 50000, 75000, 100000]),
            'quantity' => $quantity,
            'sold_quantity' => $soldQuantity,
            'sale_start_date' => $this->faker->dateTimeBetween('-2 weeks', 'now'),
            'sale_end_date' => $this->faker->dateTimeBetween('+1 week', '+2 months'),
            'benefits' => $type['benefits'],
            'location_details' => $type['location'],
            'is_featured' => $this->faker->boolean(20), // 20% chance d'être featured
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }

    public function vip(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'VIP',
            'price' => $this->faker->randomElement([75000, 100000, 150000]),
            'description' => 'Accès VIP avec avantages exclusifs et meilleures places',
            'benefits' => [
                '🍾 Boissons offertes',
                '🍽️ Buffet inclus',
                '🎟️ Accès backstage',
                '🚗 Parking VIP réservé',
                '🪑 Sièges confortables',
            ],
            'location_details' => 'Loges VIP avec vue directe sur scène',
            'is_featured' => true,
            'sort_order' => 10,
        ]);
    }

    public function standard(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Standard',
            'price' => $this->faker->randomElement([10000, 15000, 20000]),
            'description' => 'Billet d\'entrée standard avec places assises',
            'benefits' => [
                '🎫 Entrée générale',
                '🪑 Place assise garantie',
                '🚻 Accès toilettes',
            ],
            'location_details' => 'Places assises section centrale',
            'is_featured' => false,
            'sort_order' => 5,
        ]);
    }

    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Étudiant',
            'price' => $this->faker->randomElement([5000, 7500, 10000]),
            'description' => 'Tarif réduit pour étudiants (présenter carte étudiante valide)',
            'benefits' => [
                '🎓 Tarif réduit',
                '🎫 Entrée générale',
                '🚻 Accès toilettes',
            ],
            'location_details' => 'Places assises section étudiants',
            'is_featured' => false,
            'sort_order' => 1,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'sort_order' => 10,
        ]);
    }

    public function notFeatured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => false,
        ]);
    }
}
