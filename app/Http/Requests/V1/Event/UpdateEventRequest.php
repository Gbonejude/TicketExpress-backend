<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Event;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Models\Event;
use App\Rules\NoXssRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Une date de début qu'on CHANGE doit être dans le futur ; une date
     * inchangée est tolérée. Sans cette nuance, un `after:now` déclaratif
     * refuserait toute modification d'un événement déjà commencé — le formulaire
     * renvoie la date de début à chaque enregistrement, fût-elle passée.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('start_date')) {
                return;
            }

            $event = $this->route('id');
            $newStart = $this->date('start_date');

            if (! $event instanceof Event || $newStart === null) {
                return;
            }

            $current = $event->start_date;
            $unchanged = $current !== null
                && $current->format('Y-m-d H:i') === $newStart->format('Y-m-d H:i');

            if (! $unchanged && $newStart->isPast()) {
                $validator->errors()->add('start_date', 'La date et l\'heure de début doivent être dans le futur.');
            }
        });
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $eventId = $this->route('id');

        return [
            'category_id' => ['sometimes', 'string', Rule::exists('event_categories', 'id')],
            'venue_id' => ['nullable', 'string', Rule::exists('venues', 'id')],
            'title' => ['sometimes', 'string', 'max:255', new NoXssRule],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('events', 'slug')->ignore($eventId),
            ],
            'description' => ['sometimes', 'string', new NoXssRule],
            'banner' => ['nullable', 'image', 'max:2048'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'max_attendees' => ['nullable', 'integer', 'min:1'],
            'status' => ['sometimes', 'string', Rule::enum(EventStatus::class)],
            'event_type' => ['sometimes', 'string', Rule::enum(EventType::class)],
            'online_url' => ['nullable', 'url', 'max:2048', 'required_if:event_type,online'],
            'refund_allowed' => ['sometimes', 'boolean'],
            'refund_days_before' => ['sometimes', 'integer', 'min:0', 'max:365'],

            // Marges de contrôle d'accès propres à l'événement. `null` remet
            // l'événement sur les réglages de la plateforme.
            'checkin_open_hours_before' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:168'],
            'checkin_close_hours_after' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:168'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.unique' => 'Ce slug est déjà utilisé.',
            'end_date.after' => 'La date de fin doit être après la date de début.',
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'category_id' => [
                'description' => 'The ULID of the event category.',
                'example' => '01HXE2K3M4N5P6Q7R8S9T0V1W3',
            ],
            'venue_id' => [
                'description' => 'ULID of the venue.',
                'example' => '01HXE2K3M4N5P6Q7R8S9T0V1W4',
            ],
            'title' => [
                'description' => 'The title of the event.',
                'example' => 'Summer Music Festival 2024',
            ],
            'slug' => [
                'description' => 'URL-friendly slug.',
                'example' => 'summer-music-festival-2024',
            ],
            'description' => [
                'description' => 'Full description of the event.',
                'example' => 'Join us for an amazing night...',
            ],
            'banner' => [
                'description' => 'Event banner image file (max 2MB).',
                'example' => null,
            ],
            'start_date' => [
                'description' => 'Event start date and time.',
                'example' => '2024-06-15 18:00:00',
            ],
            'end_date' => [
                'description' => 'Event end date and time.',
                'example' => '2024-06-15 23:00:00',
            ],
            'max_attendees' => [
                'description' => 'Maximum number of attendees.',
                'example' => 1000,
            ],
            'status' => [
                'description' => 'Event status.',
                'example' => 'published',
            ],
        ];
    }
}
