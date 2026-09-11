<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Event;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Rules\NoXssRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreEventRequest extends FormRequest
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
            'category_id' => ['required', 'string', Rule::exists('event_categories', 'id')],
            'venue_id' => ['nullable', 'string', Rule::exists('venues', 'id')],
            'title' => ['required', 'string', 'max:255', new NoXssRule],
            'slug' => ['required', 'string', 'max:255', Rule::unique('events', 'slug')],
            'description' => ['required', 'string', new NoXssRule],
            'banner' => ['nullable', 'image', 'max:2048'],
            'start_date' => ['required', 'date', 'after:now'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'max_attendees' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', Rule::enum(EventStatus::class)],
            'event_type' => ['nullable', 'string', Rule::enum(EventType::class)],
            'online_url' => ['nullable', 'url', 'max:2048', 'required_if:event_type,online'],
            'refund_allowed' => ['nullable', 'boolean'],
            'refund_days_before' => ['nullable', 'integer', 'min:0', 'max:365'],

            // Marges de contrôle d'accès propres à l'événement. Laissées vides,
            // l'événement suit les réglages de la plateforme.
            'checkin_open_hours_before' => ['nullable', 'numeric', 'min:0', 'max:168'],
            'checkin_close_hours_after' => ['nullable', 'numeric', 'min:0', 'max:168'],
            'ticket_types' => ['nullable', 'array', 'min:1'],
            'ticket_types.*.name' => ['required_with:ticket_types', 'string', 'max:255', new NoXssRule],
            'ticket_types.*.description' => ['nullable', 'string', new NoXssRule],
            'ticket_types.*.price' => ['required_with:ticket_types', 'numeric', 'min:0'],
            'ticket_types.*.quantity' => ['required_with:ticket_types', 'integer', 'min:1'],
            'ticket_types.*.sale_start_date' => ['nullable', 'date'],
            'ticket_types.*.sale_end_date' => ['nullable', 'date', 'after:ticket_types.*.sale_start_date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'organizer_id.required' => 'L\'organisateur est requis.',
            'category_id.required' => 'La catégorie est requise.',
            'title.required' => 'Le titre est requis.',
            'slug.required' => 'Le slug est requis.',
            'slug.unique' => 'Ce slug est déjà utilisé.',
            'description.required' => 'La description est requise.',
            'start_date.required' => 'La date de début est requise.',
            'start_date.after' => 'La date et l\'heure de début doivent être dans le futur.',
            'end_date.required' => 'La date de fin est requise.',
            'end_date.after' => 'La date de fin doit être après la date de début.',
            'max_attendees.min' => 'Le nombre maximum de participants doit être au moins 1.',
            'ticket_types.min' => 'Au moins un type de ticket est requis.',
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'organizer_id' => [
                'description' => 'The ULID of the organizer creating this event.',
                'example' => '01HXE2K3M4N5P6Q7R8S9T0V1W2',
            ],
            'category_id' => [
                'description' => 'The ULID of the event category.',
                'example' => '01HXE2K3M4N5P6Q7R8S9T0V1W3',
            ],
            'venue_id' => [
                'description' => 'Optional ULID of the venue where the event takes place.',
                'example' => '01HXE2K3M4N5P6Q7R8S9T0V1W4',
            ],
            'title' => [
                'description' => 'The title of the event.',
                'example' => 'Summer Music Festival 2024',
            ],
            'slug' => [
                'description' => 'URL-friendly slug for the event.',
                'example' => 'summer-music-festival-2024',
            ],
            'description' => [
                'description' => 'Full description of the event.',
                'example' => 'Join us for an amazing night of live music...',
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
                'description' => 'Optional maximum number of attendees.',
                'example' => 1000,
            ],
            'status' => [
                'description' => 'Event status (draft, published, cancelled, finished).',
                'example' => 'draft',
            ],
            'ticket_types' => [
                'description' => 'Array of ticket types to create with the event.',
                'example' => [
                    [
                        'name' => 'VIP',
                        'description' => 'Front row seats',
                        'price' => 50000,
                        'quantity' => 100,
                        'sale_start_date' => '2024-02-01 00:00:00',
                        'sale_end_date' => '2024-06-14 23:59:59',
                    ],
                ],
            ],
        ];
    }
}
