<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Order;

use App\Enums\DeliveryMethod;
use App\Rules\InternationalPhoneRule;
use App\Rules\NoXssRule;
use App\Rules\SecureEmailRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreOrderRequest extends FormRequest
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
        return [
            'user_id' => ['nullable', 'string', Rule::exists('users', 'id')],
            'first_name' => ['required', 'string', 'max:255', new NoXssRule],
            'last_name' => ['required', 'string', 'max:255', new NoXssRule],
            'email' => ['required', 'email', 'max:255', new SecureEmailRule(checkDns: true, rejectTemporaryEmails: true)],
            'phone' => ['required', 'string', 'max:255', new InternationalPhoneRule],
            'delivery_method' => ['required', 'string', Rule::enum(DeliveryMethod::class)],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'coupon_code' => ['nullable', 'string', Rule::exists('coupons', 'code')],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ticket_type_id' => ['required', 'string', Rule::exists('ticket_types', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'Le prénom est requis.',
            'last_name.required' => 'Le nom est requis.',
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être valide.',
            'phone.required' => 'Le téléphone est requis.',
            'delivery_method.required' => 'La méthode de livraison est requise.',
            'items.required' => 'Au moins un article est requis.',
            'items.min' => 'Au moins un article est requis.',
            'items.*.ticket_type_id.required' => 'Le type de ticket est requis.',
            'items.*.ticket_type_id.exists' => 'Le type de ticket n\'existe pas.',
            'items.*.quantity.required' => 'La quantité est requise.',
            'items.*.quantity.min' => 'La quantité doit être au moins 1.',
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'user_id' => [
                'description' => 'Optional ULID of the authenticated user. If null, this is a guest checkout.',
                'example' => '01HXE2K3M4N5P6Q7R8S9T0V1W2',
            ],
            'first_name' => [
                'description' => 'First name of the buyer.',
                'example' => 'John',
            ],
            'last_name' => [
                'description' => 'Last name of the buyer.',
                'example' => 'Doe',
            ],
            'email' => [
                'description' => 'Email address of the buyer for ticket delivery.',
                'example' => 'john.doe@example.com',
            ],
            'phone' => [
                'description' => 'Phone number of the buyer.',
                'example' => '+22890123456',
            ],
            'delivery_method' => [
                'description' => 'How tickets will be delivered (email, whatsapp, both).',
                'example' => 'email',
            ],
            'payment_method' => [
                'description' => 'Optional payment method identifier.',
                'example' => 'stripe',
            ],
            'coupon_code' => [
                'description' => 'Optional coupon code for discount.',
                'example' => 'SUMMER2024',
            ],
            'items' => [
                'description' => 'Array of order items (ticket types and quantities).',
                'example' => [
                    [
                        'ticket_type_id' => '01HXE2K3M4N5P6Q7R8S9T0V1W6',
                        'quantity' => 2,
                    ],
                ],
            ],
        ];
    }
}
