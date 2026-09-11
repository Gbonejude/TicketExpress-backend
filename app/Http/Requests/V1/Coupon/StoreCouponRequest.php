<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Coupon;

use App\Enums\CouponType;
use App\Rules\NoXssRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Un coupon doit être valable dans le futur : le créer déjà expiré n'a pas
     * de sens. `after:start_date` reste déclaratif ; « fin dans le futur » vit
     * ici pour ne pas heurter la clé de message `end_date.after` déjà prise.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $end = $this->date('end_date');

            if ($end !== null && $end->isPast()) {
                $validator->errors()->add('end_date', 'La date de fin doit être dans le futur : un coupon déjà expiré ne sert à rien.');
            }
        });
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255', Rule::unique('coupons', 'code'), new NoXssRule],
            'type' => ['required', 'string', Rule::enum(CouponType::class)],
            'value' => ['required', 'numeric', 'min:0'],
            'max_usage' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'event_ids' => ['nullable', 'array'],
            'event_ids.*' => ['required_with:event_ids', 'string', Rule::exists('events', 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Le code est requis.',
            'code.unique' => 'Ce code existe déjà.',
            'type.required' => 'Le type est requis.',
            'value.required' => 'La valeur est requise.',
            'value.min' => 'La valeur doit être positive.',
            'max_usage.required' => 'Le nombre maximum d\'utilisations est requis.',
            'start_date.required' => 'La date de début est requise.',
            'start_date.after_or_equal' => 'La date de début ne peut pas être dans le passé.',
            'end_date.required' => 'La date de fin est requise.',
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
                'description' => 'Unique coupon code.',
                'example' => 'SUMMER2024',
            ],
            'type' => [
                'description' => 'Coupon type (percent or fixed).',
                'example' => 'percent',
            ],
            'value' => [
                'description' => 'Discount value (percentage or fixed amount).',
                'example' => 20,
            ],
            'max_usage' => [
                'description' => 'Maximum number of times this coupon can be used.',
                'example' => 100,
            ],
            'start_date' => [
                'description' => 'Date when the coupon becomes active.',
                'example' => '2024-06-01 00:00:00',
            ],
            'end_date' => [
                'description' => 'Date when the coupon expires.',
                'example' => '2024-08-31 23:59:59',
            ],
            'event_ids' => [
                'description' => 'Optional array of event ULIDs this coupon applies to.',
                'example' => ['01HXE2K3M4N5P6Q7R8S9T0V1W5'],
            ],
        ];
    }
}
