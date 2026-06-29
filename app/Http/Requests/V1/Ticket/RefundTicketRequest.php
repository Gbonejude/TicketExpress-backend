<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Ticket;

use App\Rules\NoXssRule;
use Illuminate\Foundation\Http\FormRequest;

final class RefundTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:500', new NoXssRule],
            'force_refund' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason.string' => 'La raison doit être une chaîne de caractères.',
            'reason.max' => 'La raison ne peut pas dépasser 500 caractères.',
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'reason' => [
                'description' => 'Optional reason for the refund request.',
                'example' => 'Je ne peux plus assister à l\'événement.',
            ],
        ];
    }
}
