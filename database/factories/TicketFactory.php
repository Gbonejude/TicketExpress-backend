<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TicketStatus;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
final class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'ticket_type_id' => TicketType::factory(),
            'attendee_name' => $this->faker->name(),
            'attendee_email' => $this->faker->safeEmail(),
            'qr_code' => $this->faker->uuid(),
            'ticket_number' => 'TKT-'.strtoupper($this->faker->bothify('????-####')),
            'status' => TicketStatus::VALID,
            'checked_in_at' => null,
            'checked_in_by' => null,
            'access_method' => 'physical',
            'online_access_link' => null,
            'refund_reason' => null,
            'refunded_at' => null,
        ];
    }

    /**
     * Indicate that the ticket has been used.
     */
    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::USED,
            'checked_in_at' => now(),
        ]);
    }

    /**
     * Indicate that the ticket has been refunded.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::REFUNDED,
            'refund_reason' => 'Demande de remboursement',
            'refunded_at' => now(),
        ]);
    }

    /**
     * Indicate that the ticket has been cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::CANCELLED,
        ]);
    }

    /**
     * Indicate that the ticket is for online access.
     */
    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_method' => 'online',
            'online_access_link' => $this->faker->url(),
        ]);
    }

    /**
     * Indicate that the ticket is for hybrid access.
     */
    public function hybrid(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_method' => 'hybrid',
            'online_access_link' => $this->faker->url(),
        ]);
    }
}
