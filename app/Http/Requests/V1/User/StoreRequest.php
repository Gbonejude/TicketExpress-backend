<?php

declare(strict_types=1);

namespace App\Http\Requests\v1\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreRequest extends FormRequest
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

        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'last_name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'birthday' => 'nullable|date|before:today',
            'gender' => 'required|string',
            'role' => ['required', 'string', 'max:255'],
            // Requis, et non plus facultatif : le mot de passe est désormais
            // généré puis envoyé par mail (CreateUserAction), donc un compte
            // sans adresse serait un compte auquel personne ne peut se
            // connecter.
            'email' => 'required|string|email|max:255|unique:users,email',
            // `password` n'est volontairement pas accepté ici : il est généré
            // côté serveur. Absent des règles, il est absent de validated() —
            // un mot de passe posté est donc ignoré, pas appliqué.
            'phone' => 'required|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:8|max:12|unique:users,phone',
            'image' => 'sometimes|nullable|image|mimes:jpg,jpeg,png,gif,svg|max:2048',

        ];
    }
}
