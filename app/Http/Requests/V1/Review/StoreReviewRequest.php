<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Review;

use App\Rules\NoXssRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreReviewRequest extends FormRequest
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
            'event_id' => ['sometimes', 'required', 'string', Rule::exists('events', 'id')],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:1000', new NoXssRule],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'event_id.required' => 'L\'événement est requis.',
            'event_id.exists' => 'L\'événement n\'existe pas.',
            'rating.required' => 'La note est requise.',
            'rating.min' => 'La note doit être entre 1 et 5.',
            'rating.max' => 'La note doit être entre 1 et 5.',
            'comment.required' => 'Le commentaire est requis.',
            'comment.max' => 'Le commentaire ne peut pas dépasser 1000 caractères.',
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'event_id' => [
                'description' => 'The ULID of the event being reviewed.',
                'example' => '01HXE2K3M4N5P6Q7R8S9T0V1W5',
            ],
            'rating' => [
                'description' => 'Rating from 1 to 5 stars.',
                'example' => 5,
            ],
            'comment' => [
                'description' => 'Review comment (max 1000 characters).',
                'example' => 'Amazing event! Highly recommend.',
            ],
        ];
    }
}
