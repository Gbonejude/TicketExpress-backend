<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Organizer;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
final class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $title = $this->faker->randomElement([
            'Festival des Arts de la Rue',
            'Concert Jazz sous les Étoiles',
            'Soirée Stand-Up Comedy',
            'Conférence Tech & Innovation',
            'Gala de Bienfaisance',
            'Marathon de Lomé',
            'Exposition d\'Art Contemporain',
            'Festival de Musique Urbaine',
            'Tournoi de Football',
            'Spectacle de Danse Traditionnelle',
        ]).' '.$this->faker->year();

        $startDate = $this->faker->dateTimeBetween('+1 week', '+3 months');
        $endDate = (clone $startDate)->modify('+'.$this->faker->numberBetween(2, 8).' hours');

        return [
            'organizer_id' => Organizer::factory(),
            'category_id' => EventCategory::factory(),
            'venue_id' => Venue::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(6),
            'description' => $this->faker->paragraphs(5, true),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'max_attendees' => $this->faker->randomElement([null, 500, 1000, 2000, 5000]),
            'status' => EventStatus::PUBLISHED,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::DRAFT,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::CANCELLED,
        ]);
    }

    public function finished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::FINISHED,
            'start_date' => $this->faker->dateTimeBetween('-3 months', '-1 week'),
            'end_date' => $this->faker->dateTimeBetween('-1 week', '-1 day'),
        ]);
    }

    /**
     * Un événement qui se déroule maintenant : commencé, pas encore terminé.
     *
     * L'état par défaut place l'événement dans plusieurs semaines, hors de la
     * fenêtre de contrôle d'accès (voir le support CheckInWindow). Tout test
     * qui valide un billet en a besoin, sans quoi il se heurte à un refus
     * « trop tôt » sans rapport avec ce qu'il cherche à vérifier.
     */
    public function ongoing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::PUBLISHED,
            'start_date' => now()->subHour(),
            'end_date' => now()->addHours(2),
        ]);
    }
}
