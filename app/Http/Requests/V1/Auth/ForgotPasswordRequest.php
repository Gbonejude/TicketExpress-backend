<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Auth;

use App\Rules\SecureEmailRule;
use Illuminate\Foundation\Http\FormRequest;

final class ForgotPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Body parameters for API documentation.
     *
     * @return array<string, array<string, string>>
     */
    public function bodyParameters(): array
    {
        return [
            'email' => [
                'description' => 'Adresse email du compte.',
                'type' => 'string',
                'example' => 'user@example.com',
            ],
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', new SecureEmailRule(checkDns: true, rejectTemporaryEmails: true)],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'L\'adresse email est requise.',
            'email.email' => 'Veuillez fournir une adresse email valide.',
            'email.max' => 'L\'adresse email ne doit pas dépasser 255 caractères.',
        ];
    }
}
