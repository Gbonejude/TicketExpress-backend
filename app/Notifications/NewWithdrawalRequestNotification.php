<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class NewWithdrawalRequestNotification extends Notification implements ShouldQueue
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
        $organizerName = $this->withdrawal->organizer?->company_name ?? 'Un organisateur';
        $formattedAmount = number_format((float) $this->withdrawal->amount, 0, ',', ' ').' FCFA';
        $methodLabel = $this->withdrawal->paymentMethodLabel();

        return [
            'title' => 'Nouvelle demande de retrait',
            'message' => "{$organizerName} a demandé un retrait de {$formattedAmount} via {$methodLabel}",
            'action' => 'view_withdrawal',
            'url' => '/withdrawals',
            'withdrawal_id' => $this->withdrawal->id,
            'organizer_id' => $this->withdrawal->organizer_id,
            'organizer_name' => $organizerName,
            'amount' => $this->withdrawal->amount,
            'phone' => $this->withdrawal->requester_phone,
            'payment_method' => $this->withdrawal->payment_method,
            'payment_method_label' => $methodLabel,
            'created_at' => now()->toISOString(),
        ];
    }
}
