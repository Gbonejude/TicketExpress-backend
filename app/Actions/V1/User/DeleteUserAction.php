<?php

declare(strict_types=1);

namespace App\Actions\V1\User;

use App\Actions\Contracts\Action;
use App\Models\User;

class DeleteUserAction implements Action
{
    /**
     * @param  array{user: User}  $data
     */
    public function execute(array $data): mixed
    {
        return (bool) $data['user']->delete();
    }
}
