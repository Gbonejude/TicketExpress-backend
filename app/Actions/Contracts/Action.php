<?php

declare(strict_types=1);

namespace App\Actions\Contracts;

interface Action
{
    /**
     * Execute the action.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): mixed;
}
