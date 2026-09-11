<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\EventOccurrence;

use App\Models\EventOccurrence;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateEventOccurrenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Deux règles, comme à la création mais tolérantes à l'inchangé :
     * — une date de début qu'on CHANGE doit être dans le futur (une
     *   représentation déjà passée reste corrigeable sans la reprogrammer) ;
     * — la représentation reste comprise dans la période de l'événement, en
     *   comblant la date absente par celle déjà en base.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $occurrence = $this->route('occurrence');

            if (! $occurrence instanceof EventOccurrence) {
                return;
            }

            if ($this->filled('start_date')) {
                $newStart = $this->date('start_date');
                $unchanged = $occurrence->start_date !== null
                    && $newStart !== null
                    && $occurrence->start_date->format('Y-m-d H:i') === $newStart->format('Y-m-d H:i');

                if (! $unchanged && $newStart !== null && $newStart->isPast()) {
                    $validator->errors()->add('start_date', 'La date et l\'heure de début doivent être dans le futur.');
                }
            }

            $event = $occurrence->event;

            if ($event === null) {
                return;
            }

            $start = $this->filled('start_date') ? $this->date('start_date') : $occurrence->start_date;
            $end = $this->filled('end_date') ? $this->date('end_date') : $occurrence->end_date;

            if ($start !== null && $event->start_date !== null && $start->lt($event->start_date)) {
                $validator->errors()->add(
                    'start_date',
                    'La représentation ne peut pas commencer avant l\'événement (le '.$event->start_date->format('d/m/Y H:i').').',
                );
            }

            if ($end !== null && $event->end_date !== null && $end->gt($event->end_date)) {
                $validator->errors()->add(
                    'end_date',
                    'La représentation ne peut pas finir après l\'événement (le '.$event->end_date->format('d/m/Y H:i').').',
                );
            }
        });
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
