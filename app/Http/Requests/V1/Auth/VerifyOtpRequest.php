<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $phone
 * @property string $code
 */
final class VerifyOtpRequest extends FormRequest
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
            'code' => [
                'description' => 'The 6-digit OTP code received by the user.',
                'type' => 'string',
                'example' => '482910',
            ],
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^\+228[0-9]{8}$/'],
            'code' => ['required', 'string', 'digits:6'],
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
            'code.required' => 'The OTP code is required.',
            'code.digits' => 'The OTP code must be exactly 6 digits.',
        ];
    }
}
