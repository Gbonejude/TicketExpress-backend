<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Coupon;

use App\Enums\CouponType;
use App\Rules\NoXssRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
