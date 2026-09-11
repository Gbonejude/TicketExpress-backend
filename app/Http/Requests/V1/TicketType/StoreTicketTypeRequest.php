<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\TicketType;

use App\Support\TicketDateWindow;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreTicketTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Emboîtement des fenêtres : événement ⊇ vente ⊇ promotion. L'événement est
     * porté par la route (`/events/{event}/ticket-types`).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            TicketDateWindow::check($validator, $this->route('event'), [
                'sale_start' => $this->date('sale_start_date'),
                'sale_end' => $this->date('sale_end_date'),
                'promo_start' => $this->date('promotion_start_date'),
                'promo_end' => $this->date('promotion_end_date'),
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
            // Nom unique au sein de l'événement : deux « VIP » sur la même
            // affiche prêtent à confusion à la billetterie comme au guichet.
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('ticket_types', 'name')->where('event_id', $this->route('event')?->id),
            ],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
            'sale_start_date' => ['nullable', 'date'],
            'sale_end_date' => ['nullable', 'date', 'after:sale_start_date'],
            'benefits' => ['nullable', 'array'],
            'benefits.*' => ['string', 'max:500'],
            'location_details' => ['nullable', 'string', 'max:500'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            // Promotion fields
            'promotional_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'promotion_start_date' => ['nullable', 'date', 'required_with:promotional_price'],
            'promotion_end_date' => ['nullable', 'date', 'after:promotion_start_date', 'required_with:promotional_price'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du type de ticket est requis.',
            'name.unique' => 'Un type de billet porte déjà ce nom pour cet événement.',
            'price.required' => 'Le prix est requis.',
            'price.min' => 'Le prix doit être positif.',
            'quantity.required' => 'La quantité est requise.',
            'quantity.min' => 'La quantité doit être au moins 1.',
            'sale_end_date.after' => 'La date de fin doit être après la date de début.',
            'promotional_price.lt' => 'Le prix promotionnel doit être inférieur au prix normal.',
            'promotional_price.min' => 'Le prix promotionnel doit être positif.',
            'promotion_start_date.required_with' => 'La date de début de promotion est requise.',
            'promotion_end_date.required_with' => 'La date de fin de promotion est requise.',
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
                'description' => 'The name of the ticket type (e.g., VIP, Standard, Early Bird).',
                'example' => 'VIP',
            ],
            'description' => [
                'description' => 'Optional description of what this ticket type includes.',
                'example' => 'Front row seats with backstage access',
            ],
            'price' => [
                'description' => 'Price per ticket in the local currency.',
                'example' => 50000,
            ],
            'quantity' => [
                'description' => 'Total number of tickets available for this type.',
                'example' => 100,
            ],
            'sale_start_date' => [
                'description' => 'Optional date when sales start for this ticket type.',
                'example' => '2024-02-01 00:00:00',
            ],
            'sale_end_date' => [
                'description' => 'Optional date when sales end for this ticket type.',
                'example' => '2024-02-28 23:59:59',
            ],
            'benefits' => [
                'description' => 'Optional array of benefits included with this ticket type.',
                'example' => ['Boissons offertes', 'Buffet inclus', 'Accès backstage'],
            ],
            'location_details' => [
                'description' => 'Optional details about the seating/location area.',
                'example' => 'Loges VIP avec vue directe sur scène',
            ],
            'is_featured' => [
                'description' => 'Whether this ticket type should be featured/highlighted.',
                'example' => true,
            ],
            'sort_order' => [
                'description' => 'Display order (lower values displayed first).',
                'example' => 10,
            ],
        ];
    }
}
