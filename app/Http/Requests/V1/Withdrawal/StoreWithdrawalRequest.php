<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Withdrawal;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreWithdrawalRequest extends FormRequest
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
            'organizer_id' => ['required', 'string', Rule::exists('organizers', 'id')],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'organizer_id.required' => 'L\'organisateur est requis.',
            'organizer_id.exists' => 'L\'organisateur n\'existe pas.',
            'amount.required' => 'Le montant est requis.',
            'amount.min' => 'Le montant doit être au moins 1.',
            'payment_method.required' => 'La méthode de paiement est requise.',
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'organizer_id' => [
                'description' => 'The ULID of the organizer requesting withdrawal.',
                'example' => '01HXE2K3M4N5P6Q7R8S9T0V1W2',
            ],
            'amount' => [
                'description' => 'Amount to withdraw.',
                'example' => 500000,
            ],
            'payment_method' => [
                'description' => 'Payment method for withdrawal (bank transfer, mobile money, etc.).',
                'example' => 'bank_transfer',
            ],
        ];
    }
}
