<?php

declare(strict_types=1);

namespace App\Actions\V1\Auth;

use App\Actions\Contracts\Action;
use App\Models\User;

/**
 * Back-office login.
 *
 * The credential check itself lives in {@see LoginAction}; this class exists so
 * the dashboard keeps its own entry point and can gain back-office-specific
 * rules (role restrictions, audit logging) without touching the public flow.
 */
class AdminLoginAction implements Action
{
    public function __construct(private readonly LoginAction $login) {}

    /**
     * @param  array{email: string, password: string}  $data
     */
    public function execute(array $data): User
    {
        return $this->login->execute($data);
    }
}
