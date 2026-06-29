<?php

declare(strict_types=1);

namespace App\Actions\V1\Ticket;

use App\Actions\Contracts\Action;
use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Enums\TicketStatus;
use App\Events\Order\OrderRefundedEvent;
use App\Events\Ticket\TicketRefundedEvent;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

final class RefundTicketAction implements Action
{
    /**
     * Refund a ticket with business rule validation.
     *
     * @param  array{
     *     ticket: Ticket,
     *     reason?: string|null,
     *     force_refund?: bool
     * }  $data
     */
    public function execute(array $data): Ticket
    {
        /** @var Ticket $ticket */
        $ticket = $data['ticket'];
        $reason = $data['reason'] ?? 'Demande de remboursement client';
        $forceRefund = $data['force_refund'] ?? false;

        /** @var TicketStatus $currentStatus */
        $currentStatus = $ticket->getAttribute('status');

        // Verify ticket status
        if ($currentStatus !== TicketStatus::VALID) {
            throw new \DomainException('Ce ticket ne peut pas être remboursé (statut invalide).');
        }

        // Load necessary relationships
        $ticket->load(['order', 'ticketType.event']);
        $order = $ticket->order;
        $event = $ticket->ticketType->event;

        /** @var EventStatus $eventStatus */
        $eventStatus = $event->getAttribute('status');

        // Check if event is cancelled (automatic refund)
        $isEventCancelled = $eventStatus === EventStatus::CANCELLED;

        if (! $isEventCancelled && ! $forceRefund) {
            // Voluntary refund - check conditions

            // 1. Check 30-day deadline
            $daysBefore = now()->diffInDays($event->start_date, false);
            if ($daysBefore < 30) {
                throw new \DomainException('Les remboursements doivent être demandés au moins 30 jours avant l\'événement.');
            }

            // 2. Check if a coupon was used
            // We check if any ticket in this order belongs to an event that has coupons
            // and if the total_amount suggests a discount was applied
            $orderTickets = $order->tickets()->with('ticketType')->get();
            $expectedTotal = $orderTickets->sum(fn ($t) => $t->ticketType->price);

            // If order total is less than expected, a coupon was likely used
            if ($order->total_amount < $expectedTotal) {
                throw new \DomainException('Les tickets achetés avec un code promo ne sont pas remboursables.');
            }
        }

        return DB::transaction(function () use ($ticket, $order, $reason): Ticket {
            // Mark ticket as refunded
            $ticket->update([
                'status' => TicketStatus::REFUNDED,
                'refund_reason' => $reason,
                'refunded_at' => now(),
            ]);

            // Trigger TicketRefundedEvent
            event(new TicketRefundedEvent($ticket));

            // Check if all tickets in the order are refunded
            $allRefunded = $order->tickets()
                ->where('status', '!=', TicketStatus::REFUNDED->value)
                ->count() === 0;

            if ($allRefunded) {
                // Refund the entire order
                $order->update([
                    'status' => OrderStatus::REFUNDED,
                    'refunded_at' => now(),
                ]);

                event(new OrderRefundedEvent($order));
            }

            return $ticket->fresh() ?? $ticket;
        });
    }
}
