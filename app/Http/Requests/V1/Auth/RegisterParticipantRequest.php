<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Auth;

use App\Rules\InternationalPhoneRule;
use App\Rules\NoXssRule;
use App\Rules\SecureEmailRule;
use Illuminate\Foundation\Http\FormRequest;

final class RegisterParticipantRequest extends FormRequest
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
            'first_name' => [
                'description' => 'Prénom du participant.',
                'type' => 'string',
                'example' => 'Jean',
            ],
            'last_name' => [
                'description' => 'Nom de famille du participant.',
                'type' => 'string',
                'example' => 'Dupont',
            ],
            'email' => [
                'description' => 'Adresse email du participant.',
                'type' => 'string',
                'example' => 'jean.dupont@example.com',
            ],
            'phone' => [
                'description' => 'Numéro de téléphone au format international.',
                'type' => 'string',
                'example' => '+22890000000',
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
            'first_name' => ['required', 'string', 'max:255', new NoXssRule],
            'last_name' => ['required', 'string', 'max:255', new NoXssRule],
            'email' => ['required', 'string', 'email', 'max:255', new SecureEmailRule(checkDns: true, rejectTemporaryEmails: true)],
            'phone' => ['required', 'string', 'max:20', new InternationalPhoneRule],
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
            'first_name.required' => 'Le prénom est requis.',
            'first_name.max' => 'Le prénom ne doit pas dépasser 255 caractères.',
            'last_name.required' => 'Le nom de famille est requis.',
            'last_name.max' => 'Le nom de famille ne doit pas dépasser 255 caractères.',
            'email.required' => 'L\'adresse email est requise.',
            'email.email' => 'Veuillez fournir une adresse email valide.',
            'email.max' => 'L\'adresse email ne doit pas dépasser 255 caractères.',
            'phone.required' => 'Le numéro de téléphone est requis.',
            'phone.max' => 'Le numéro de téléphone ne doit pas dépasser 20 caractères.',
        ];
    }
}
