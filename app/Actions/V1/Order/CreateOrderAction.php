<?php

declare(strict_types=1);

namespace App\Actions\V1\Order;

use App\Actions\Contracts\Action;
use App\Enums\CouponType;
use App\Enums\OrderStatus;
use App\Events\Order\OrderCreatedEvent;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\TicketType;
use Illuminate\Support\Facades\DB;

final class CreateOrderAction implements Action
{
    /**
     * Create an order with atomic stock management.
     *
     * @param  array{
     *     user_id?: string|null,
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     *     phone: string,
     *     delivery_method: string,
     *     payment_method?: string|null,
     *     coupon_code?: string|null,
     *     items: array<int, array{
     *         ticket_type_id: string,
     *         quantity: int
     *     }>
     * }  $data
     */
    public function execute(array $data): Order
    {
        return DB::transaction(function () use ($data): Order {
            $items = $data['items'];
            $coupon = null;

            // Validate and apply coupon if provided
            if (! empty($data['coupon_code'])) {
                $coupon = Coupon::where('code', $data['coupon_code'])->first();

                if (! $coupon) {
                    throw new \DomainException('Le code promo n\'existe pas.');
                }

                if ($coupon->used_count >= $coupon->max_usage) {
                    throw new \DomainException('Ce code promo a atteint sa limite d\'utilisation.');
                }

                if (now()->lt($coupon->start_date) || now()->gt($coupon->end_date)) {
                    throw new \DomainException('Ce code promo n\'est pas valide pour cette période.');
                }
            }

            $totalAmount = 0;
            $orderItems = [];

            // Process each item with atomic stock validation
            foreach ($items as $item) {
                $ticketType = TicketType::query()
                    ->where('id', $item['ticket_type_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $ticketType) {
                    throw new \DomainException('Le type de ticket n\'existe pas.');
                }

                // La billetterie est-elle encore ouverte ? Rien ne le vérifiait :
                // le stock seul décidait, donc un événement terminé — ou dont la
                // vente était fermée — encaissait toujours. C'est ici que la
                // règle doit vivre, et pas seulement dans l'affichage : le
                // catalogue peut masquer l'événement, une requête directe sur cet
                // endpoint passe outre.
                $this->assertSaleIsOpen($ticketType);

                $available = $ticketType->quantity - $ticketType->sold_quantity;

                if ($available < $item['quantity']) {
                    throw new \DomainException(
                        sprintf(
                            'Stock insuffisant pour "%s". Disponible: %d, Demandé: %d',
                            $ticketType->name,
                            $available,
                            $item['quantity'],
                        ),
                    );
                }

                // Increment sold_quantity atomically
                $ticketType->increment('sold_quantity', $item['quantity']);

                // Le prix effectif, promotion comprise — et non `price`.
                //
                // La caisse facturait le tarif plein pendant que la fiche de
                // l'événement affichait le prix promotionnel et que le panier le
                // totalisait : le client voyait « 3 750 F » et était débité
                // 5 000 F. C'est `currentPrice()` qui porte la règle (promotion
                // active seulement dans sa fenêtre), et c'est déjà elle que lisent
                // l'affichage, le tri par prix et le filtre de prix.
                $unitPrice = $ticketType->currentPrice();

                $subtotal = $unitPrice * $item['quantity'];
                $totalAmount += $subtotal;

                $orderItems[] = [
                    'ticket_type_id' => $ticketType->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];
            }

            // Apply coupon discount if applicable
            if ($coupon) {
                /** @var CouponType $couponType */
                $couponType = $coupon->getAttribute('type');

                if ($couponType->value === 'percent') {
                    $discount = $totalAmount * ($coupon->value / 100);
                    $totalAmount -= $discount;
                } else {
                    // Fixed discount
                    $totalAmount -= $coupon->value;
                }

                // Ensure total is not negative
                $totalAmount = max($totalAmount, 0);

                // Increment coupon usage
                $coupon->increment('used_count');
            }

            // Create order
            $order = Order::create([
                'user_id' => $data['user_id'] ?? null,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'total_amount' => $totalAmount,
                'status' => OrderStatus::PENDING,
                'payment_method' => $data['payment_method'] ?? null,
                'delivery_method' => $data['delivery_method'],
            ]);

            // Create order items
            foreach ($orderItems as $orderItem) {
                $order->items()->create($orderItem);
            }

            $order->load('items.ticketType');

            // Dispatch OrderCreatedEvent AFTER transaction commit
            event(new OrderCreatedEvent($order));

            // Real-time (silent) refresh signals for the back-office lists.
            DB::afterCommit(function () use ($order): void {
                \App\Events\ResourceChangedEvent::dispatchQuietly('orders', 'created', $order->id);
                \App\Events\ResourceChangedEvent::dispatchQuietly('tickets', 'created', null);
                \App\Events\ResourceChangedEvent::dispatchQuietly('payments', 'created', null);
            });

            return $order;
        });
    }

    /**
     * Refuse une ligne dont la billetterie est fermée.
     *
     * Trois motifs distincts, parce que trois messages différents : « pas encore
     * ouvert » invite à revenir, « terminé » et « fermé » non. Un acheteur à qui
     * l'on répond « stock insuffisant » sur un concert de l'an dernier chercherait
     * à recharger la page.
     *
     * L'événement est consulté explicitement, et non déduit de la fenêtre de
     * vente : un tarif sans `sale_end_date` sur un événement passé serait sinon
     * encore vendable, et c'est le cas de tous les tarifs créés avant que ces
     * dates ne soient renseignées.
     *
     * @throws \DomainException
     */
    private function assertSaleIsOpen(TicketType $ticketType): void
    {
        $event = $ticketType->loadMissing('event')->event;

        if ($event !== null && $event->end_date !== null && $event->end_date->isPast()) {
            throw new \DomainException(
                sprintf('L\'événement « %s » est terminé : la billetterie est fermée.', $event->title),
            );
        }

        if ($ticketType->sale_start_date !== null && now()->lt($ticketType->sale_start_date)) {
            throw new \DomainException(
                sprintf(
                    'La vente de « %s » ouvre le %s.',
                    $ticketType->name,
                    $ticketType->sale_start_date->format('d/m/Y à H:i'),
                ),
            );
        }

        if (! $ticketType->isOnSale()) {
            throw new \DomainException(
                sprintf('La vente de « %s » est fermée.', $ticketType->name),
            );
        }
    }
}
