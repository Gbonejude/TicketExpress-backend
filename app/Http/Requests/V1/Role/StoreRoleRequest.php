<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Role;

use App\Enums\Screen;
use App\Rules\NoXssRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreRoleRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100', 'unique:roles,name', new NoXssRule],
            'label' => ['nullable', 'string', 'max:150', new NoXssRule],
            'screens' => ['array'],
            'screens.*' => ['string', Rule::in(Screen::keys())],
            'actions' => ['array'],
            'actions.*' => ['string', Rule::in(Screen::allActionPermissions())],
        ];
    }
}
