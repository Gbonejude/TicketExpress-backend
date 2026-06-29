<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Organizer;

use App\Enums\OrganizerStatus;
use App\Rules\NoXssRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateOrganizerRequest extends FormRequest
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
            'company_name' => ['sometimes', 'string', 'max:255', new NoXssRule],
            'description' => ['sometimes', 'string', new NoXssRule],
            'logo' => ['nullable', 'image', 'max:2048'],
            'website' => ['nullable', 'url', 'max:255'],
            'status' => ['sometimes', 'string', Rule::enum(OrganizerStatus::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'company_name.string' => 'Le nom de l\'entreprise doit être une chaîne de caractères.',
            'website.url' => 'Le site web doit être une URL valide.',
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'company_name' => [
                'description' => 'The name of the organizing company.',
                'example' => 'EventPro Inc.',
            ],
            'description' => [
                'description' => 'A detailed description of the organizer.',
                'example' => 'Professional event organization company.',
            ],
            'logo' => [
                'description' => 'The organizer logo image file (max 2MB).',
                'example' => null,
            ],
            'website' => [
                'description' => 'The organizer\'s website URL.',
                'example' => 'https://eventpro.com',
            ],
            'status' => [
                'description' => 'The approval status of the organizer.',
                'example' => 'approved',
            ],
        ];
    }
}
