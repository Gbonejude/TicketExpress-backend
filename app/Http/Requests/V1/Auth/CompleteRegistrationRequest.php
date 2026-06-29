<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class CompleteRegistrationRequest extends FormRequest
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
            'phone' => [
                'description' => 'The user phone number in Togolese format.',
                'type' => 'string',
                'example' => '+22890123456',
            ],
            'first_name' => [
                'description' => 'The user first name.',
                'type' => 'string',
                'example' => 'Simon',
            ],
            'last_name' => [
                'description' => 'The user last name.',
                'type' => 'string',
                'example' => 'Dev',
            ],
            'image' => [
                'description' => 'The image of the user.',
                'type' => 'file',
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
            'address' => [
                'description' => 'Optional user address.',
                'type' => 'string',
                'example' => 'Lomé, Togo',
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
            'phone' => ['required', 'string', 'regex:/^\+228[0-9]{8}$/'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'],
            'birthday' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', 'in:male,female'],
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
            'phone.required' => 'The phone number is required.',
            'phone.regex' => 'The phone number must be a valid Togolese number (+228XXXXXXXX).',
            'first_name.required' => 'The first name is required.',
            'last_name.required' => 'The last name is required.',
            'image.image' => 'The uploaded file must be an image.',
            'image.max' => 'The image size must not exceed 2MB.',
            'birthday.date' => 'The birthday must be a valid date.',
            'birthday.before' => 'The birthday must be a date before today.',
            'gender.in' => 'The gender must be either male or female.',

        ];
    }
}
