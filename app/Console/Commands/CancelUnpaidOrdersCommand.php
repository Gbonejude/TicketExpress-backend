<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\V1\Order\CancelOrderAction;
use App\Enums\OrderStatus;
use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

/**
 * Annule les commandes restées impayées, et rend les places au stock.
 *
 * `sold_quantity` est incrémenté dès la **création** de la commande, pas au
 * paiement : c'est ce qui empêche deux acheteurs de se disputer la même place
 * pendant qu'ils règlent. Mais rien ne libérait jamais ces places quand l'un des
 * deux abandonnait son panier — un tarif finissait par afficher « Complet » sans
 * qu'un seul billet ait été vendu, et l'organisateur perdait des ventes qu'il ne
 * pouvait pas expliquer.
 *
 * Quinze minutes après sa création, une commande non payée est donc annulée
 * (délai fixé par le client). C'est confortable pour un paiement mobile money —
 * saisir un code sur son téléphone prend une minute ou deux — et court assez
 * pour qu'un événement qui se remplit ne soit pas bloqué par des paniers morts.
 *
 * @see CancelOrderAction c'est elle qui décrémente le stock et notifie.
 */
final class CancelUnpaidOrdersCommand extends Command
{
    /**
     * Le délai accordé, en minutes.
     *
     * Une constante et non un réglage : c'est une règle de la plateforme, la même
     * pour tous, et la rendre configurable inviterait à la changer sans mesurer
     * qu'elle borne aussi la fenêtre de course avec le retour de l'opérateur.
     */
    public const GRACE_MINUTES = 15;

    protected $signature = 'orders:cancel-unpaid
                            {--dry-run : Compter les commandes concernées sans rien annuler}';

    protected $description = 'Annuler les commandes non payées depuis plus de 15 minutes et libérer leurs places.';

    public function handle(CancelOrderAction $cancel): int
    {
        $cutoff = CarbonImmutable::now()->subMinutes(self::GRACE_MINUTES);
        $isDryRun = (bool) $this->option('dry-run');

        $matched = 0;
        $cancelled = 0;

        $this->candidates($cutoff)->chunkById(200, function ($orders) use (&$matched, &$cancelled, $isDryRun, $cancel): void {
            foreach ($orders as $order) {
                $matched++;

                if ($isDryRun) {
                    continue;
                }

                try {
                    $cancel->execute(['order' => $order]);
                    $cancelled++;
                } catch (\DomainException $e) {
                    // La commande a été payée ou annulée entre la requête et ici.
                    // Ce n'est pas une anomalie : c'est la course qu'on voulait
                    // perdre proprement, en laissant gagner le paiement.
                    Log::info('Commande impayée déjà résolue, annulation ignorée', [
                        'order_id' => $order->id,
                        'reason' => $e->getMessage(),
                    ]);
                }
            }
        });

        if ($matched === 0) {
            $this->components->info('Aucune commande impayée à annuler.');

            return self::SUCCESS;
        }

        $this->components->info($isDryRun
            ? "{$matched} commande(s) seraient annulée(s)."
            : "{$cancelled} commande(s) annulée(s), places rendues au stock.");

        return self::SUCCESS;
    }

    /**
     * Les commandes à annuler : en attente, créées avant la borne, et sans
     * tentative de paiement récente.
     *
     * La seconde condition est celle qui évite de voler une place à quelqu'un en
     * train de payer. Un paiement mobile money peut revenir plusieurs minutes
     * après avoir été lancé : annuler pendant ce temps rendrait les places au
     * stock, puis le retour de l'opérateur repasserait la commande à « payée » —
     * on aurait vendu deux fois le même siège. Chaque tentative rouvre donc un
     * délai plein.
     *
     * @return Builder<Order>
     */
    private function candidates(CarbonImmutable $cutoff): Builder
    {
        return Order::query()
            ->where('status', OrderStatus::PENDING->value)
            ->where('created_at', '<', $cutoff)
            ->whereDoesntHave('payments', function (Builder $payment) use ($cutoff): void {
                $payment->where('updated_at', '>=', $cutoff);
            });
    }
}
