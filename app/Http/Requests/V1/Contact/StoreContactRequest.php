<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Contact;

use App\Rules\NoXssRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreContactRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120', new NoXssRule],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:150', new NoXssRule],
            'message' => ['required', 'string', 'min:10', 'max:5000', new NoXssRule],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est requis.',
            'email.required' => 'L\'adresse e-mail est requise.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'subject.required' => 'L\'objet est requis.',
            'message.required' => 'Le message est requis.',
            'message.min' => 'Le message doit faire au moins 10 caractères.',
            'message.max' => 'Le message ne peut pas dépasser 5000 caractères.',
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'Full name of the person writing.',
                'example' => 'Koffi Mensah',
            ],
            'email' => [
                'description' => 'Address the reply goes to.',
                'example' => 'koffi.mensah@example.com',
            ],
            'phone' => [
                'description' => 'Optional phone number.',
                'example' => '+228 90 12 34 56',
            ],
            'subject' => [
                'description' => 'What the message is about.',
                'example' => 'Support billetterie',
            ],
            'message' => [
                'description' => 'The message itself.',
                'example' => 'Bonjour, je n\'ai pas reçu mon billet par WhatsApp.',
            ],
        ];
    }
}
