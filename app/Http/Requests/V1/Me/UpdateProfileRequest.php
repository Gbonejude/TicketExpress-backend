<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Me;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Les champs qu'un compte peut changer chez lui-même.
 *
 * Volontairement plus étroit que `User\UpdateRequest` : ni `role`, ni
 * `password`. Le rôle relève de l'administration — l'accepter ici reviendrait à
 * laisser chacun se promouvoir — et le mot de passe passe par « mot de passe
 * oublié », le seul chemin où il ne transite par personne d'autre que son
 * propriétaire.
 */
final class UpdateProfileRequest extends FormRequest
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
        $id = $this->user()?->id;

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($id)],
            'phone' => [
                'nullable', 'string', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:8', 'max:12',
                Rule::unique('users')->ignore($id),
            ],

            // La photo arrive par POST + `_method=PUT` : un navigateur n'envoie
            // pas de multipart en PUT.
            'image' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'Le prénom est requis.',
            'last_name.required' => 'Le nom est requis.',
            'email.required' => 'L\'adresse e-mail est requise.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'phone.unique' => 'Ce numéro est déjà utilisé.',
            'phone.regex' => 'Le numéro ne doit contenir que des chiffres.',
            'phone.min' => 'Le numéro doit contenir au moins 8 chiffres.',
            'phone.max' => 'Le numéro ne doit pas dépasser 12 chiffres.',
            'image.image' => 'Le fichier doit être une image.',
            'image.mimes' => 'Formats acceptés : JPG, PNG, GIF ou WebP.',
            'image.max' => 'L\'image ne doit pas dépasser 2 Mo.',
        ];
    }
}
