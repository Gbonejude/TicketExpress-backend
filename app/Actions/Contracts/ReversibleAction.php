<?php

declare(strict_types=1);

namespace App\Actions\Contracts;

interface ReversibleAction extends Action
{
    /**
     * Rollback the action's side effects.
     *
     * @param  array<string, mixed>  $data
     */
    public function rollback(array $data): void;
}
