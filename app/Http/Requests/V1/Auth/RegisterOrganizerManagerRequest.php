<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Auth;

use App\Rules\NoXssRule;
use App\Rules\SecureEmailRule;
use App\Rules\SecurePasswordRule;
use App\Rules\TogoPhoneRule;
use Illuminate\Foundation\Http\FormRequest;

final class RegisterOrganizerManagerRequest extends FormRequest
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
                'description' => 'Prénom du gestionnaire d\'organisateur.',
                'type' => 'string',
                'example' => 'Marie',
            ],
            'last_name' => [
                'description' => 'Nom de famille du gestionnaire d\'organisateur.',
                'type' => 'string',
                'example' => 'Kouassi',
            ],
            'email' => [
                'description' => 'Adresse email du gestionnaire d\'organisateur.',
                'type' => 'string',
                'example' => 'marie.kouassi@example.com',
            ],
            'phone' => [
                'description' => 'Numéro de téléphone au format international.',
                'type' => 'string',
                'example' => '+22890000000',
            ],
            'password' => [
                'description' => 'Mot de passe du gestionnaire (minimum 8 caractères).',
                'type' => 'string',
                'example' => 'Password123!',
            ],
            'password_confirmation' => [
                'description' => 'Confirmation du mot de passe.',
                'type' => 'string',
                'example' => 'Password123!',
            ],
            'company_name' => [
                'description' => 'Nom de la société organisatrice.',
                'type' => 'string',
                'example' => 'Events Pro Togo',
            ],
            'description' => [
                'description' => 'Description de l\'organisateur (optionnel).',
                'type' => 'string',
                'example' => 'Spécialiste des événements corporatifs et culturels.',
            ],
            'website' => [
                'description' => 'Site web de l\'organisateur (optionnel).',
                'type' => 'string',
                'example' => 'https://www.eventsprotogo.com',
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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email', new SecureEmailRule(checkDns: true, rejectTemporaryEmails: true)],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone', new TogoPhoneRule],
            'password' => ['required', 'string', 'min:8', 'confirmed', new SecurePasswordRule],
            'company_name' => ['required', 'string', 'max:255', new NoXssRule],
            'description' => ['nullable', 'string', 'max:1000', new NoXssRule],
            'website' => ['nullable', 'url', 'max:255'],
            // The organizer's logo, sent with the application. Optional: a
            // missing logo must not block someone from applying, and the
            // back-office lets them add one later.
            'logo' => ['nullable', 'image', 'max:2048'],
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
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'phone.required' => 'Le numéro de téléphone est requis.',
            'phone.max' => 'Le numéro de téléphone ne doit pas dépasser 20 caractères.',
            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'company_name.required' => 'Le nom de la société est requis.',
            'company_name.max' => 'Le nom de la société ne doit pas dépasser 255 caractères.',
            'description.max' => 'La description ne doit pas dépasser 1000 caractères.',
            'website.url' => 'Veuillez fournir une URL valide.',
            'website.max' => 'L\'URL du site web ne doit pas dépasser 255 caractères.',
        ];
    }
}
