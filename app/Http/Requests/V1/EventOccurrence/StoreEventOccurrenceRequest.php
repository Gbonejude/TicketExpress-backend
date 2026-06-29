<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\EventOccurrence;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEventOccurrenceRequest extends FormRequest
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
            'event_id' => ['required', 'string', 'exists:events,id'],
            'start_date' => ['required', 'date', 'after:now'],
            'end_date' => ['required', 'date', 'after:start_date'],
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
            'event_id.required' => 'L\'identifiant de l\'événement est requis.',
            'event_id.exists' => 'L\'événement spécifié n\'existe pas.',
            'start_date.required' => 'La date de début est requise.',
            'start_date.after' => 'La date de début doit être dans le futur.',
            'end_date.required' => 'La date de fin est requise.',
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
            'event_id' => [
                'description' => 'ID of the event this occurrence belongs to.',
                'example' => '01HXE...',
            ],
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
                'description' => 'Optional notes specific to this occurrence.',
                'example' => 'Session du vendredi soir',
            ],
        ];
    }
}
