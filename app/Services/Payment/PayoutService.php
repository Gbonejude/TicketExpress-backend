<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\Withdrawal;
use Illuminate\Support\Facades\Log;

/**
 * Versement d'un retrait vers le numéro mobile money de l'organisateur.
 *
 * **Le transfert lui-même est manuel, et ce n'est pas un choix.** PayGate, tel
 * qu'intégré (voir PayGateService), n'expose que l'encaissement : `pay` pousse
 * une demande de paiement sur le téléphone d'un client pour qu'il paie la
 * plateforme, et rien ne fait l'inverse. Aucun endpoint de décaissement, aucune
 * clé dédiée dans la configuration. Un administrateur fait donc le virement
 * depuis le compte Flooz / Mix by Yas de la plateforme, et enregistre la
 * référence rendue par l'opérateur.
 *
 * Ce service existe pour deux raisons :
 *
 * 1. **Vérifier avant d'engager.** `check-balance` existe, lui : on peut refuser
 *    d'approuver un retrait que le solde marchand ne couvre pas, plutôt que de
 *    le découvrir au moment de payer.
 * 2. **Tenir la place de l'automatisation.** Le jour où PayGate ouvre un accès de
 *    décaissement, `transfer()` est le seul endroit à remplir : le circuit, les
 *    autorisations, la traçabilité et l'interface n'ont pas à bouger.
 */
final class PayoutService
{
    public function __construct(
        private readonly PayGateService $payGate,
    ) {}

    /**
     * Le solde marchand disponible, ou null s'il n'a pas pu être lu.
     *
     * Null n'est pas 0 : l'IP du serveur doit être whitelistée chez PayGate, et
     * l'appel échoue tant qu'elle ne l'est pas. Confondre les deux bloquerait
     * toute approbation en développement.
     */
    public function availableFloat(string $paymentMethod): ?float
    {
        $payload = $this->payGate->balance();

        if (isset($payload['error'])) {
            Log::warning('Solde PayGate illisible', [
                'error' => $payload['error'],
                'method' => $paymentMethod,
            ]);

            return null;
        }

        // La réponse de check-balance n'est pas documentée par un schéma stable.
        // On cherche donc la clé de l'opérateur concerné parmi les formes
        // rencontrées, et on renonce plutôt que de deviner.
        $keys = $paymentMethod === 'flooz'
            ? ['flooz', 'FLOOZ', 'flooz_balance']
            : ['tmoney', 'TMONEY', 'tmoney_balance'];

        foreach ($keys as $key) {
            if (isset($payload[$key]) && is_numeric($payload[$key])) {
                return (float) $payload[$key];
            }
        }

        Log::warning('Solde PayGate : clé opérateur absente de la réponse', [
            'method' => $paymentMethod,
            'keys' => array_keys($payload),
        ]);

        return null;
    }

    /**
     * Le solde couvre-t-il ce retrait ?
     *
     * Renvoie null pour « on ne sait pas », et l'appelant laisse alors passer.
     * Deux cas : le contrôle est désactivé, ou le solde est illisible.
     *
     * Le contrôle est désactivé par défaut (`paygate.enforce_payout_balance`) car
     * `check-balance` répond un solde nul quand la clé est absente ou l'IP non
     * whitelistée — bloquer là-dessus rendrait toute approbation impossible en
     * développement. Aucun appel HTTP n'est même tenté dans ce cas : approuver un
     * retrait ne doit pas dépendre de la disponibilité d'un service externe.
     */
    public function covers(Withdrawal $withdrawal): ?bool
    {
        if (! config('services.paygate.enforce_payout_balance')) {
            return null;
        }

        $available = $this->availableFloat((string) $withdrawal->payment_method);

        if ($available === null) {
            return null;
        }

        return $available >= (float) $withdrawal->amount;
    }

    /**
     * Exécute le versement.
     *
     * Aujourd'hui : consigne l'intention et rend la main, le virement étant fait
     * par un humain depuis le compte de la plateforme. Le retrait ne passe à
     * « payé » que lorsque cet humain saisit la référence de l'opérateur — c'est
     * elle, et pas cet appel, qui prouve que l'argent est parti.
     *
     * Demain : appeler ici l'API de décaissement, et retourner sa référence.
     *
     * @return array{automated: bool, reference: string|null}
     */
    public function transfer(Withdrawal $withdrawal): array
    {
        Log::info('Versement à effectuer', [
            'withdrawal' => $withdrawal->id,
            'organizer' => $withdrawal->organizer_id,
            'amount' => $withdrawal->amount,
            'to' => $withdrawal->requester_phone,
            'method' => $withdrawal->payment_method,
        ]);

        return ['automated' => false, 'reference' => null];
    }
}
