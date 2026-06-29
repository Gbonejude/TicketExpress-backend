<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Auth;

use App\Rules\SecureEmailRule;
use App\Rules\SecurePasswordRule;
use Illuminate\Foundation\Http\FormRequest;

final class ResetPasswordRequest extends FormRequest
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
            'token' => [
                'description' => 'Token de réinitialisation reçu par email.',
                'type' => 'string',
                'example' => 'abc123xyz789',
            ],
            'password' => [
                'description' => 'Nouveau mot de passe (minimum 8 caractères).',
                'type' => 'string',
                'example' => 'NewPassword123!',
            ],
            'password_confirmation' => [
                'description' => 'Confirmation du nouveau mot de passe.',
                'type' => 'string',
                'example' => 'NewPassword123!',
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
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed', new SecurePasswordRule],
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
            'token.required' => 'Le token de réinitialisation est requis.',
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ];
    }
}
