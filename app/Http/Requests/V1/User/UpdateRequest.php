<?php

declare(strict_types=1);

namespace App\Http\Requests\v1\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Retrieves the body parameters for the function.
     *
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'last_name' => [
                'description' => 'The last name of the user.',
                'type' => 'string',
                'example' => 'Doe',
            ],
            'first_name' => [
                'description' => 'The first name of the user.',
                'type' => 'string',
                'example' => 'John',
            ],
            'birthday' => [
                'description' => 'The birth date of the user.',
                'type' => 'date',
                'example' => '1990-01-01',
            ],
            'gender' => [
                'description' => 'The gender of the user.',
                'type' => 'string',
                'example' => 'male',
            ],
            'role' => [
                'description' => 'The role of the user.',
                'type' => 'string',
                'example' => 'admin',
            ],
            'email' => [
                'description' => 'The email address of the user.',
                'type' => 'string',
                'example' => 'johnny@example.com',
            ],
            'phone' => [
                'description' => 'The phone number of the user.',
                'type' => 'string',
                'example' => '22893413639',
            ],
            'image' => [
                'description' => 'The image of the user.',
                'type' => 'file',
            ],
            'address.place_id' => [
                'description' => 'The unique identifier of the address.',
                'type' => 'string',
                'example' => 'ChIJN1t_tDeuEmsRUsoyG83frY4',
            ],
            'address.place_name' => [
                'description' => 'The name of the place associated with the address.',
                'type' => 'string',
                'example' => 'Eiffel Tower',
            ],
            'address.longitude' => [
                'description' => 'The longitude of the address.',
                'type' => 'number',
                'example' => 2.2945,
            ],
            'address.latitude' => [
                'description' => 'The latitude of the address.',
                'type' => 'number',
                'example' => 48.8584,
            ],
            'address.street_name' => [
                'description' => 'The street name of the address.',
                'type' => 'string',
                'example' => 'Champ de Mars',
            ],
            'address.phone' => [
                'description' => 'A unique phone number associated with the address.',
                'type' => 'string',
                'example' => '93078910',
            ],
            'address.user_id' => [
                'description' => 'The unique identifier for the user.',
                'type' => 'string',
                'example' => '01F8Z9H7K5PK11V2GZ2D7QZX8A',
            ],

        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'last_name' => ['nullable', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'birthday' => 'nullable|date|before:today',
            'gender' => 'nullable|string',
            'role' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($id)],
            // Pas de `password` ici : un administrateur ne saisit plus le mot de
            // passe d'autrui. Celui de la création part par mail, et son
            // renouvellement passe par le mot de passe oublié — le seul chemin
            // où le mot de passe ne transite par personne d'autre que son
            // propriétaire.
            'phone' => ['nullable', 'string', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:8', 'max:12', Rule::unique('users')->ignore($id)],
            'image' => 'sometimes|nullable|image|mimes:jpg,jpeg,png,gif,svg|max:2048',

            'address' => ['sometimes', 'array'],
            'address.place_id' => ['nullable', 'string'],
            'address.place_name' => ['nullable', 'string', 'max:255'],
            'address.longitude' => ['nullable', 'numeric'],
            'address.latitude' => ['nullable', 'numeric'],
            'address.street_name' => ['nullable', 'string', 'max:255'],
            'address.phone' => ['nullable', 'string', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:8', 'max:12', Rule::unique('addresses')->ignore($id)],
        ];
    }
}
