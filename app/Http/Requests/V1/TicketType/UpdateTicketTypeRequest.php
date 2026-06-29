<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\TicketType;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateTicketTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'sale_start_date' => ['nullable', 'date'],
            'sale_end_date' => ['nullable', 'date', 'after:sale_start_date'],
            'benefits' => ['nullable', 'array'],
            'benefits.*' => ['string', 'max:500'],
            'location_details' => ['nullable', 'string', 'max:500'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            // Promotion fields
            'promotional_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'promotion_start_date' => ['nullable', 'date'],
            'promotion_end_date' => ['nullable', 'date', 'after:promotion_start_date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'price.min' => 'Le prix doit être positif.',
            'quantity.min' => 'La quantité doit être au moins 1.',
            'sale_end_date.after' => 'La date de fin doit être après la date de début.',
            'promotional_price.lt' => 'Le prix promotionnel doit être inférieur au prix normal.',
            'promotional_price.min' => 'Le prix promotionnel doit être positif.',
            'promotion_end_date.after' => 'La date de fin de promotion doit être après la date de début.',
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'The name of the ticket type.',
                'example' => 'VIP',
            ],
            'description' => [
                'description' => 'Description of what this ticket type includes.',
                'example' => 'Front row seats with backstage access',
            ],
            'price' => [
                'description' => 'Price per ticket.',
                'example' => 50000,
            ],
            'quantity' => [
                'description' => 'Total number of tickets available.',
                'example' => 100,
            ],
            'sale_start_date' => [
                'description' => 'Date when sales start.',
                'example' => '2024-02-01 00:00:00',
            ],
            'sale_end_date' => [
                'description' => 'Date when sales end.',
                'example' => '2024-02-28 23:59:59',
            ],
            'benefits' => [
                'description' => 'Array of benefits included with this ticket type.',
                'example' => ['Boissons offertes', 'Buffet inclus'],
            ],
            'location_details' => [
                'description' => 'Details about the seating/location area.',
                'example' => 'Loges VIP avec vue directe sur scène',
            ],
            'is_featured' => [
                'description' => 'Whether this ticket type should be featured.',
                'example' => true,
            ],
            'sort_order' => [
                'description' => 'Display order (lower values first).',
                'example' => 10,
            ],
        ];
    }
}
