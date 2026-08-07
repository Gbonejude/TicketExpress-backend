<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Organizer;

use App\Enums\OrganizerStatus;
use App\Rules\NoXssRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreOrganizerRequest extends FormRequest
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
            'user_id' => ['required', 'string', Rule::exists('users', 'id')],
            'company_name' => ['required', 'string', 'max:255', new NoXssRule],
            'description' => ['required', 'string', new NoXssRule],
            'logo' => ['nullable', 'image', 'max:2048'],
            'website' => ['nullable', 'url', 'max:255'],
            'status' => ['nullable', 'string', Rule::enum(OrganizerStatus::class)],

            // Marges de contrôle d'accès appliquées par défaut à ses
            // événements. Plafonnées à une semaine : au-delà, la fenêtre ne
            // borne plus rien.
            'checkin_open_hours_before' => ['nullable', 'numeric', 'min:0', 'max:168'],
            'checkin_close_hours_after' => ['nullable', 'numeric', 'min:0', 'max:168'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'L\'utilisateur est requis.',
            'user_id.exists' => 'L\'utilisateur n\'existe pas.',
            'company_name.required' => 'Le nom de l\'entreprise est requis.',
            'description.required' => 'La description est requise.',
            'website.url' => 'Le site web doit être une URL valide.',
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'user_id' => [
                'description' => 'The ULID of the user who owns this organizer profile.',
                'example' => '01HXE2K3M4N5P6Q7R8S9T0V1W2',
            ],
            'company_name' => [
                'description' => 'The name of the organizing company.',
                'example' => 'EventPro Inc.',
            ],
            'description' => [
                'description' => 'A detailed description of the organizer.',
                'example' => 'Professional event organization company specializing in concerts and festivals.',
            ],
            'logo' => [
                'description' => 'The organizer logo image file (max 2MB).',
                'example' => null,
            ],
            'website' => [
                'description' => 'The organizer\'s website URL.',
                'example' => 'https://eventpro.com',
            ],
            'status' => [
                'description' => 'The approval status of the organizer.',
                'example' => 'pending',
            ],
        ];
    }
}
