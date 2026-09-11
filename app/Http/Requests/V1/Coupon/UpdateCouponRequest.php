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
            if (! $this->filled('end_date')) {
                return;
            }

            $coupon = $this->route('id');
            $newEnd = $this->date('end_date');

            if (! $coupon instanceof Coupon || $newEnd === null) {
                return;
            }

            $current = $coupon->end_date;
            $unchanged = $current !== null
                && $current->format('Y-m-d H:i') === $newEnd->format('Y-m-d H:i');

            if (! $unchanged && $newEnd->isPast()) {
                $validator->errors()->add('end_date', 'La date de fin doit être dans le futur.');
            }

            // La validité ne dépasse pas la fin de l'événement visé — celui
            // envoyé si présent, sinon celui déjà rattaché au coupon.
            $event = $this->filled('event_ids')
                ? Event::find($this->input('event_ids.0'))
                : $coupon->events->first();

            if ($event?->end_date !== null && $newEnd->gt($event->end_date)) {
                $validator->errors()->add('end_date', 'La date de fin ne peut pas dépasser la fin de l\'événement (le '.$event->end_date->format('d/m/Y H:i').').');
            }
        });
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $couponId = $this->route('coupon');

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
