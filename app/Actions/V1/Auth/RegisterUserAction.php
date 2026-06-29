<?php

declare(strict_types=1);

namespace App\Actions\V1\Auth;

use App\Actions\Contracts\Action;
use App\Exceptions\ApiException;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class RegisterUserAction implements Action
{
    public function __construct() {}

    /**
     * @param  array{
     *     phone: string,
     *     first_name: string,
     *     last_name: string,
     *     email?: string|null,
     *     address?: string|null,
     *     birthday?: string|null,
     *     gender?: string|null,
     *     image?: UploadedFile|null,
     * }  $data
     */
    public function execute(array $data): mixed
    {
        $verifiedOtp = OtpCode::where('phone', $data['phone'])
            ->whereNotNull('verified_at')
            ->where('verified_at', '>', now()->subMinutes(15))
            ->latest()
            ->first();

        if (! $verifiedOtp) {
            throw new ApiException('Phone number not verified or session has expired.', 422);
        }

        if (User::where('phone', $data['phone'])->exists()) {
            throw new ApiException('An account already exists for this phone number.', 409);
        }

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'birthday' => $data['birthday'] ?? null,
            'gender' => $data['gender'] ?? null,
        ]);

        if (isset($data['image'])) {
            $user->addMedia($data['image'])
                ->toMediaCollection(collectionName: 'users');
        }

        return $user;
    }
}
