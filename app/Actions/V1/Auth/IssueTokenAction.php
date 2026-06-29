<?php

namespace App\Actions\V1\Auth;

use App\Actions\Contracts\Action;
use App\Models\User;

class IssueTokenAction implements Action
{
    /**
     * @param  array{user: User, device_name?: string}  $data
     */
    public function execute(array $data): mixed
    {
        /** @var User $user */
        $user = $data['user'];
        $deviceName = $data['device_name'] ?? 'mobile';

        return $user->createToken($deviceName)->plainTextToken;
    }
}
