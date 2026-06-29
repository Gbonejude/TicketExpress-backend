<?php

declare(strict_types=1);

namespace App\Actions\V1\Auth;

use App\Actions\Contracts\Action;
use App\Models\User;

class LogoutAction implements Action
{
    /**
     * @param  array{user: User}  $data
     */
    public function execute(array $data): mixed
    {
        /** @var User $user */
        $user = $data['user'];

        $user->currentAccessToken()->delete();

        return true;
    }
}
