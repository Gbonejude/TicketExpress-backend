<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\WithdrawalStatus;
use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class WithdrawalProcessedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Withdrawal $withdrawal,
    ) {
        $this->queue = 'notifications';
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $formattedAmount = number_format((float) $this->withdrawal->amount, 0, ',', ' ').' FCFA';
        $methodLabel = $this->withdrawal->paymentMethodLabel();

        $title = match ($this->withdrawal->status) {
            WithdrawalStatus::APPROVED => 'Demande de retrait approuvée',
            WithdrawalStatus::PAID => 'Retrait versé avec succès',
            WithdrawalStatus::REJECTED => 'Demande de retrait refusée',
            default => 'Mise à jour de votre retrait',
        };

        $message = match ($this->withdrawal->status) {
            WithdrawalStatus::APPROVED => "Votre demande de retrait de {$formattedAmount} via {$methodLabel} a été approuvée.",
            WithdrawalStatus::PAID => "Votre retrait de {$formattedAmount} via {$methodLabel} a été payé.".($this->withdrawal->payout_reference ? " (Réf: {$this->withdrawal->payout_reference})" : ''),
            WithdrawalStatus::REJECTED => "Votre demande de retrait de {$formattedAmount} a été refusée.".($this->withdrawal->notes ? " Motif: {$this->withdrawal->notes}" : ''),
            default => "Votre demande de retrait de {$formattedAmount} a été traitée.",
        };

        $type = match ($this->withdrawal->status) {
            WithdrawalStatus::APPROVED, WithdrawalStatus::PAID => 'success',
            WithdrawalStatus::REJECTED => 'error',
            default => 'info',
        };

        return [
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'action' => 'view_withdrawal',
            'url' => '/withdrawals',
            'withdrawal_id' => $this->withdrawal->id,
            'amount' => $this->withdrawal->amount,
            'status' => $this->withdrawal->status->value,
            'status_label' => $this->withdrawal->status->label(),
            'payment_method' => $this->withdrawal->payment_method,
            'payment_method_label' => $methodLabel,
            'payout_reference' => $this->withdrawal->payout_reference,
            'notes' => $this->withdrawal->notes,
            'created_at' => now()->toISOString(),
        ];
    }
}
