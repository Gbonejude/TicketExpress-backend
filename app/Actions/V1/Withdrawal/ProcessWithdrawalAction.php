<?php

declare(strict_types=1);

namespace App\Actions\V1\Withdrawal;

use App\Actions\Contracts\Action;
use App\Enums\WithdrawalStatus;
use App\Models\User;
use App\Models\Withdrawal;

final class ProcessWithdrawalAction implements Action
{
    /**
     * Fait avancer un retrait dans son circuit, et garde trace de l'auteur.
     *
     * Les transitions permises sont portées par l'enum plutôt que par une suite
     * de `if` : le circuit n'est décrit qu'à un seul endroit, et l'interface s'en
     * sert pour n'offrir que les statuts réellement atteignables.
     *
     * @param  array{withdrawal: Withdrawal, status: string, notes?: string|null, payout_reference?: string|null, actor?: User|null}  $data
     */
    public function execute(array $data): Withdrawal
    {
        $withdrawal = $data['withdrawal'];
        $current = $withdrawal->status;
        $next = WithdrawalStatus::from($data['status']);

        if ($current->isFinal()) {
            throw new \DomainException(sprintf(
                'Ce retrait est %s : son statut ne change plus.',
                mb_strtolower($current->label()),
            ));
        }

        if (! $current->canTransitionTo($next)) {
            throw new \DomainException(sprintf(
                'Passage de « %s » à « %s » impossible : un retrait doit être approuvé avant d\'être payé.',
                $current->label(),
                $next->label(),
            ));
        }

        $withdrawal->update([
            'status' => $next,
            'notes' => $data['notes'] ?? $withdrawal->notes,
            // La référence n'accompagne que le passage à « payé » : c'est la
            // preuve du virement, elle n'a pas de sens sur une approbation.
            'payout_reference' => $next === WithdrawalStatus::PAID
                ? ($data['payout_reference'] ?? $withdrawal->payout_reference)
                : $withdrawal->payout_reference,
            'processed_at' => now(),
            'processed_by' => ($data['actor'] ?? null)?->id,
        ]);

        return $withdrawal->fresh(['organizer', 'processedBy']);
    }
}
