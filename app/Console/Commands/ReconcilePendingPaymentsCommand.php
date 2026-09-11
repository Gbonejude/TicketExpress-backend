<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\V1\Payment\MarkPaymentPaidAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Services\Payment\PayGateService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

/**
 * Réconcilie les paiements PayGate restés « en attente ».
 *
 * Le callback de PayGate n'arrive pas toujours (URL injoignable en local, aléa
 * réseau, retour opérateur tardif) : sans cela, un client débité voit sa
 * commande bloquée en « en attente » et n'a aucun billet. Cette commande
 * interroge PayGate pour chaque paiement encore en attente et, s'il est en
 * réalité payé, le marque payé via {@see MarkPaymentPaidAction} — ce qui émet
 * les billets.
 *
 * Elle tourne chaque minute, donc bien avant la borne de 15 minutes de
 * `orders:cancel-unpaid` : un paiement réussi est réconcilié avant que ses
 * places ne soient rendues au stock.
 */
final class ReconcilePendingPaymentsCommand extends Command
{
    /**
     * On ne remonte pas au-delà : passé la borne d'annulation (15 min), la
     * commande est de toute façon tranchée. Une marge confortable rattrape un
     * retour d'opérateur tardif sans interroger PayGate pour de vieux paniers.
     */
    private const LOOKBACK_MINUTES = 60;

    protected $signature = 'payments:reconcile-pending
                            {--dry-run : Interroger PayGate sans rien enregistrer}';

    protected $description = 'Réconcilier auprès de PayGate les paiements en attente et émettre les billets des commandes réellement payées.';

    public function handle(PayGateService $payGate, MarkPaymentPaidAction $markPaid): int
    {
        $since = CarbonImmutable::now()->subMinutes(self::LOOKBACK_MINUTES);
        $isDryRun = (bool) $this->option('dry-run');

        $checked = 0;
        $reconciled = 0;

        Payment::query()
            ->where('status', PaymentStatus::NON_PAYE->value)
            ->where('created_at', '>=', $since)
            ->whereHas('order', fn (Builder $q) => $q->where('status', OrderStatus::PENDING->value))
            ->with('order')
            ->chunkById(100, function ($payments) use ($payGate, $markPaid, $isDryRun, &$checked, &$reconciled): void {
                foreach ($payments as $payment) {
                    $checked++;

                    $status = $payment->transaction_reference
                        ? $payGate->status($payment->transaction_reference)
                        : $payGate->statusByIdentifier($payment->id);

                    if (isset($status['error'])) {
                        Log::warning('Réconciliation PayGate : statut indisponible', [
                            'payment_id' => $payment->id,
                            'error' => $status['error'],
                        ]);

                        continue;
                    }

                    if ((int) ($status['status'] ?? -1) !== PayGateService::PAYMENT_SUCCESS) {
                        continue;
                    }

                    $reconciled++;

                    if (! $isDryRun) {
                        $markPaid->execute(['payment' => $payment, 'datetime' => $status['datetime'] ?? null]);
                    }
                }
            });

        $this->components->info($isDryRun
            ? "{$reconciled}/{$checked} paiement(s) en attente sont en réalité payés."
            : "{$reconciled}/{$checked} paiement(s) réconcilié(s), billets émis.");

        return self::SUCCESS;
    }
}
