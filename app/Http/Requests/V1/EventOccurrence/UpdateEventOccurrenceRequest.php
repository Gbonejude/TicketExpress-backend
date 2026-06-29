<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\EventOccurrence;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateEventOccurrenceRequest extends FormRequest
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
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'max_attendees' => ['nullable', 'integer', 'min:1'],
            'status' => ['sometimes', 'string', 'in:active,sold_out,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'end_date.after' => 'La date de fin doit être après la date de début.',
            'max_attendees.min' => 'La capacité maximale doit être au moins 1.',
            'status.in' => 'Le statut doit être: active, sold_out, ou cancelled.',
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'start_date' => [
                'description' => 'Start date and time of the occurrence.',
                'example' => '2026-07-31 19:00:00',
            ],
            'end_date' => [
                'description' => 'End date and time of the occurrence.',
                'example' => '2026-07-31 23:00:00',
            ],
            'max_attendees' => [
                'description' => 'Maximum number of attendees (null = unlimited).',
                'example' => 5000,
            ],
            'status' => [
                'description' => 'Status of the occurrence.',
                'example' => 'active',
            ],
            'notes' => [
                'description' => 'Notes specific to this occurrence.',
                'example' => 'Session du vendredi soir - mise à jour',
            ],
        ];
    }
}
