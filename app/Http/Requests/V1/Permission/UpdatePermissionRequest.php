<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Permission;

use App\Rules\NoXssRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePermissionRequest extends FormRequest
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
        $permissionId = $this->route('permission');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')->ignore($permissionId),
                new NoXssRule,
            ],
            'guard_name' => ['sometimes', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'Ce nom de permission existe déjà.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'The permission name.',
                'example' => 'screen.updated_feature',
            ],
            'guard_name' => [
                'description' => 'The guard name (defaults to web).',
                'example' => 'web',
            ],
        ];
    }
}
