<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Me;

use App\Rules\SecurePasswordRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Changer son mot de passe depuis son compte.
 *
 * `current_password` est exigé : sans lui, un poste laissé ouvert suffirait à
 * verrouiller le compte de son propriétaire. C'est ce qui distingue ce chemin de
 * « mot de passe oublié », où la preuve est le lien reçu par e-mail.
 */
final class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password', new SecurePasswordRule],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Le mot de passe actuel est requis.',
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
            'password.required' => 'Le nouveau mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation ne correspond pas.',
            'password.different' => 'Le nouveau mot de passe doit être différent de l\'actuel.',
        ];
    }
}
