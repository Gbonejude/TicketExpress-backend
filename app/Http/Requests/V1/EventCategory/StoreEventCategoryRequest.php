<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\EventCategory;

use App\Rules\NoXssRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreEventCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', new NoXssRule],
            'slug' => ['required', 'string', 'max:255', Rule::unique('event_categories', 'slug')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la catégorie est requis.',
            'slug.required' => 'Le slug est requis.',
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
