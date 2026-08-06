<?php

declare(strict_types=1);

namespace App\Mail;

use App\Enums\WithdrawalStatus;
use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Informe l'organisateur du sort de sa demande de retrait.
 *
 * Un même mail pour les trois issues : approuvé, payé, rejeté. Le sujet et le
 * corps s'adaptent, parce que ce qui compte diffère — un virement parti se lit à
 * sa référence, un refus à son motif.
 */
final class WithdrawalProcessedMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Withdrawal $withdrawal,
    ) {
        $this->onQueue('emails');
    }

    public function envelope(): Envelope
    {
        $subject = match ($this->withdrawal->status) {
            WithdrawalStatus::APPROVED => 'Votre demande de retrait est approuvée - TicketExpress',
            WithdrawalStatus::PAID => 'Votre retrait a été versé - TicketExpress',
            WithdrawalStatus::REJECTED => 'Votre demande de retrait a été refusée - TicketExpress',
            WithdrawalStatus::PENDING => 'Votre demande de retrait - TicketExpress',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.withdrawal_processed',
            with: [
                'companyName' => $this->withdrawal->organizer?->company_name ?? 'Organisateur',
                'status' => $this->withdrawal->status,
                'statusLabel' => $this->withdrawal->status->label(),
                'amount' => number_format((float) $this->withdrawal->amount, 0, ',', ' '),
                'phone' => $this->withdrawal->requester_phone,
                'method' => $this->withdrawal->payment_method === 'flooz' ? 'Flooz' : 'Mix by Yas',
                'reference' => $this->withdrawal->payout_reference,
                'notes' => $this->withdrawal->notes,
            ],
        );
    }
}
