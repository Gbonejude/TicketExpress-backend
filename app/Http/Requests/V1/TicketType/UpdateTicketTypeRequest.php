<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\TicketType;

use App\Models\TicketType;
use App\Support\TicketDateWindow;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateTicketTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Même emboîtement qu'à la création, mais tolérant à l'inchangé : une date
     * absente de la requête garde sa valeur en base, sinon modifier le seul
     * prix reviendrait à revalider — et parfois refuser — des dates qu'on n'a
     * pas touchées.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $current = $this->route('id');
            $current = $current instanceof TicketType ? $current : null;

            $keep = fn (string $field, ?string $attr) => $this->filled($field)
                ? $this->date($field)
                : $current?->{$attr};

            TicketDateWindow::check($validator, $this->route('event'), [
                'sale_start' => $keep('sale_start_date', 'sale_start_date'),
                'sale_end' => $keep('sale_end_date', 'sale_end_date'),
                'promo_start' => $keep('promotion_start_date', 'promotion_start_date'),
                'promo_end' => $keep('promotion_end_date', 'promotion_end_date'),
            ]);
        });
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'occurrence_id' => ['nullable', 'string', 'exists:event_occurrences,id'],
            // Nom unique au sein de l'événement, le billet courant excepté.
            'name' => [
                'sometimes', 'string', 'max:255',
                Rule::unique('ticket_types', 'name')
                    ->where('event_id', $this->route('event')?->id)
                    ->ignore($this->route('id')),
            ],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'gt:0'],
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
            'name.unique' => 'Un type de billet porte déjà ce nom pour cet événement.',
            'price.gt' => 'Le prix doit être supérieur à 0.',
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
