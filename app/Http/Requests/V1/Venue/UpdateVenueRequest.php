<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Venue;

use App\Rules\NoXssRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateVenueRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255', new NoXssRule],
            'address' => ['sometimes', 'string', new NoXssRule],
            'city' => ['sometimes', 'string', 'max:255', new NoXssRule],
            'country' => ['sometimes', 'string', 'max:255', new NoXssRule],
            'capacity' => ['sometimes', 'integer', 'min:1'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.string' => 'Le nom doit être une chaîne de caractères.',
            'capacity.min' => 'La capacité doit être au moins 1.',
            'latitude.between' => 'La latitude doit être entre -90 et 90.',
            'longitude.between' => 'La longitude doit être entre -180 et 180.',
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'The name of the venue.',
                'example' => 'Grand Arena',
            ],
            'address' => [
                'description' => 'The full address of the venue.',
                'example' => '123 Main Street',
            ],
            'city' => [
                'description' => 'The city where the venue is located.',
                'example' => 'Lomé',
            ],
            'country' => [
                'description' => 'The country where the venue is located.',
                'example' => 'Togo',
            ],
            'capacity' => [
                'description' => 'Maximum number of attendees the venue can hold.',
                'example' => 5000,
            ],
            'latitude' => [
                'description' => 'GPS latitude coordinate of the venue.',
                'example' => 6.1319,
            ],
            'longitude' => [
                'description' => 'GPS longitude coordinate of the venue.',
                'example' => 1.2228,
            ],
        ];
    }
}
