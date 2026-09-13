<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Coupon;

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Models\Event;
use App\Rules\NoXssRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Une date de fin qu'on CHANGE doit être dans le futur ; inchangée, elle est
     * tolérée (on corrige le code d'un coupon expiré sans le reprogrammer).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $coupon = $this->route('id') ?? $this->route('coupon');

            if (is_string($coupon)) {
                $coupon = Coupon::find($coupon);
            }

            if (! $coupon instanceof Coupon) {
                return;
            }

            // ── Validation date de fin ──────────────────────────────────────
            if ($this->filled('end_date')) {
                $newEnd = $this->date('end_date');

                if ($newEnd !== null) {
                    $current   = $coupon->end_date;
                    $unchanged = $current !== null
                        && $current->format('Y-m-d H:i') === $newEnd->format('Y-m-d H:i');

                    if (! $unchanged && $newEnd->isPast()) {
                        $validator->errors()->add('end_date', 'La date de fin doit être dans le futur.');
                    }

                    // La validité ne dépasse pas la fin de l'événement visé.
                    $event = $this->filled('event_ids')
                        ? Event::find($this->input('event_ids.0'))
                        : $coupon->events->first();

                    if ($event?->end_date !== null && $newEnd->gt($event->end_date)) {
                        $validator->errors()->add('end_date', 'La date de fin ne peut pas dépasser la fin de l\'événement (le '.$event->end_date->format('d/m/Y H:i').').');
                    }
                }
            }

            // ── Validation valeur de réduction ─────────────────────────────
            // S'exécute même si end_date n'est pas envoyé.
            if ($this->filled('value') || $this->filled('type')) {
                $type  = $this->filled('type')  ? $this->input('type')          : $coupon->type?->value;
                $value = $this->filled('value') ? (float) $this->input('value') : (float) $coupon->value;

                // Pourcentage plafonné à 100.
                if ($type === 'percent' && $value > 100) {
                    $validator->errors()->add('value', 'Un coupon de type pourcentage ne peut pas dépasser 100 %.');
                }

                // Montant fixe : ne doit pas dépasser le prix minimum des billets.
                if ($type === 'fixed' && $value > 0) {
                    $targetEvent = $this->filled('event_ids')
                        ? Event::find($this->input('event_ids.0'))
                        : $coupon->events->first();

                    if ($targetEvent !== null) {
                        $minPrice = $targetEvent->ticketTypes()->min('price');

                        if ($minPrice !== null && $value > (float) $minPrice) {
                            $validator->errors()->add(
                                'value',
                                'Le montant fixe ('.$value.' FCFA) dépasse le prix minimum des billets de cet événement ('.$minPrice.' FCFA). Réduisez la remise.',
                            );
                        }
                    }
                }
            }
        });
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $coupon = $this->route('id') ?? $this->route('coupon');
        $couponId = $coupon instanceof Coupon ? $coupon->id : $coupon;

        return [
            'code' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('coupons', 'code')->ignore($couponId),
                new NoXssRule,
            ],
            'type' => ['sometimes', 'string', Rule::enum(CouponType::class)],
            'value' => ['sometimes', 'numeric', 'min:0'],
            'max_usage' => ['sometimes', 'integer', 'min:1'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
            'event_ids' => ['sometimes', 'array', 'size:1'],
            'event_ids.*' => ['string', Rule::exists('events', 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'Ce code existe déjà.',
            'value.min' => 'La valeur doit être positive.',
            'end_date.after' => 'La date de fin doit être après la date de début.',
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'code' => [
                'description' => 'Coupon code.',
                'example' => 'SUMMER2024',
            ],
            'type' => [
                'description' => 'Coupon type (percent or fixed).',
                'example' => 'percent',
            ],
            'value' => [
                'description' => 'Discount value.',
                'example' => 20,
            ],
            'max_usage' => [
                'description' => 'Maximum usage count.',
                'example' => 100,
            ],
            'start_date' => [
                'description' => 'Start date.',
                'example' => '2024-06-01 00:00:00',
            ],
            'end_date' => [
                'description' => 'End date.',
                'example' => '2024-08-31 23:59:59',
            ],
            'event_ids' => [
                'description' => 'Array of event ULIDs.',
                'example' => ['01HXE2K3M4N5P6Q7R8S9T0V1W5'],
            ],
        ];
    }
}
