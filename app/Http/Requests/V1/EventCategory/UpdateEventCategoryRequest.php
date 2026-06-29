<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\EventCategory;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateEventCategoryRequest extends FormRequest
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
        $categoryId = $this->route('event_category');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('event_categories', 'slug')->ignore($categoryId),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.string' => 'Le nom doit être une chaîne de caractères.',
            'slug.unique' => 'Ce slug est déjà utilisé.',
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'The name of the event category.',
                'example' => 'Music',
            ],
            'slug' => [
                'description' => 'The URL-friendly slug for the category.',
                'example' => 'music',
            ],
        ];
    }
}
