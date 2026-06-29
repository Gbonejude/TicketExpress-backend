<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\User;

use App\Enums\Screen;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserScreensRequest extends FormRequest
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
            'screens' => ['required', 'array'],
            'screens.*' => ['required', 'string', Rule::in(Screen::keys())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'screens.required' => 'Le champ screens est requis.',
            'screens.array' => 'Le champ screens doit être un tableau.',
            'screens.*.required' => 'Chaque screen est requis.',
            'screens.*.string' => 'Chaque screen doit être une chaîne.',
            'screens.*.in' => 'Screen invalide.',
        ];
    }
}
