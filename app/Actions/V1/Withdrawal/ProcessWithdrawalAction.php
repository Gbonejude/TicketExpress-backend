<?php

declare(strict_types=1);

namespace App\Actions\V1\Withdrawal;

use App\Actions\Contracts\Action;
use App\Enums\WithdrawalStatus;
use App\Models\Withdrawal;

final class ProcessWithdrawalAction implements Action
{
    /**
     * Process a withdrawal (approve, reject, or mark as paid).
     *
     * @param  array{withdrawal: Withdrawal, status: string}  $data
     */
    public function execute(array $data): Withdrawal
    {
        $withdrawal = $data['withdrawal'];
        $newStatus = WithdrawalStatus::from($data['status']);

        if ($withdrawal->status === WithdrawalStatus::PAID) {
            throw new \DomainException('Ce retrait a déjà été payé.');
        }

        if ($withdrawal->status === WithdrawalStatus::REJECTED) {
            throw new \DomainException('Ce retrait a été rejeté.');
        }

        $withdrawal->update(['status' => $newStatus]);

        return $withdrawal->fresh();
    }
}
