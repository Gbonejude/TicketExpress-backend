<?php

declare(strict_types=1);

namespace App\Actions\V1\Payment;

use App\Actions\Contracts\Action;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\Order\OrderPaidEvent;
use App\Events\ResourceChangedEvent;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

/**
 * Enregistre un paiement réussi : marque le paiement payé, la commande payée,
 * et **émet les billets**.
 *
 * Point unique du passage à « payé », partagé par le callback PayGate, le
 * rafraîchissement de statut, et la réconciliation planifiée. Auparavant cette
 * logique vivait en privé dans le contrôleur et, surtout, ne dispatchait jamais
 * `OrderPaidEvent` : une commande pouvait passer « payée » sans qu'aucun billet
 * ne soit créé — « Mes billets » restait vide. C'est ce dispatch qui déclenche
 * `OrderPaidListener` (billets + e-mail de confirmation).
 *
 * Idempotent : un paiement déjà « payé » ne refait rien, donc le callback, le
 * polling et la réconciliation peuvent tous l'appeler sans doublonner les
 * billets.
 */
final class MarkPaymentPaidAction implements Action
{
    /**
     * @param  array{payment: Payment, datetime?: string|null}  $data
     */
    public function execute(array $data): mixed
    {
        $payment = $data['payment'];
        $datetime = $data['datetime'] ?? null;

        if ($payment->status === PaymentStatus::PAYE) {
            return $payment;
        }

        $paidAt = $datetime !== null && $datetime !== '' ? $datetime : now();

        $payment->update([
            'status' => PaymentStatus::PAYE,
            'paid_at' => $paidAt,
        ]);

        $order = $payment->order;

        // Le paiement est enregistré — l'argent est bien arrivé — mais une
        // commande annulée ne repasse pas à « payée » toute seule : ses places
        // ont pu être revendues. On trace en `error` pour qu'un humain traite le
        // remboursement, plutôt que de vendre deux fois le même siège.
        if ($order !== null && $order->status === OrderStatus::CANCELLED) {
            Log::error('Paiement reçu sur une commande annulée — remboursement à traiter', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
            ]);

            ResourceChangedEvent::dispatchQuietly('payments', 'paid', $payment->id);

            return $payment;
        }

        $order?->update([
            'status' => OrderStatus::PAID,
            'paid_at' => $paidAt,
        ]);

        // Émettre les billets et envoyer la confirmation. Sans ce dispatch, la
        // commande devenait « payée » sans le moindre billet.
        if ($order !== null) {
            OrderPaidEvent::dispatch($order);
        }

        ResourceChangedEvent::dispatchQuietly('payments', 'paid', $payment->id);
        ResourceChangedEvent::dispatchQuietly('orders', 'paid', $payment->order_id);

        return $payment;
    }
}
