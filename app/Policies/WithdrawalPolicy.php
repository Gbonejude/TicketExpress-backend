<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Withdrawal;

/**
 * Autorisations des retraits.
 *
 * Un retrait fait sortir de l'argent de la plateforme, d'où le partage suivant :
 * un organisateur demande et consulte ses propres retraits, l'administration
 * décide. Approuver ou payer sa propre demande n'a pas de sens, ces deux gestes
 * restent donc hors de portée d'un organisateur — les admins passent par le
 * `before()` du trait.
 *
 * Ces règles existaient déjà mais n'étaient appelées de nulle part, et `create()`
 * vérifiait le rôle « manager », qui n'existe pas : aucun organisateur n'aurait
 * pu déposer de demande si la policy avait été branchée.
 */
final class WithdrawalPolicy
{
    use AdminBypassesAll;

    /** Voir la liste : l'administration, ou un organisateur pour la sienne. */
    public function viewAny(User $user): bool
    {
        return $this->isOrganizer($user);
    }

    public function view(User $user, Withdrawal $withdrawal): bool
    {
        return $this->isOwner($user, $withdrawal);
    }

    /** Demander un retrait suppose un compte organisateur. */
    public function create(User $user): bool
    {
        return $this->isOrganizer($user);
    }

    /**
     * Supprimer : l'organisateur peut retirer sa demande tant qu'elle n'a pas
     * été traitée. Une fois approuvée, elle appartient à l'historique.
     */
    public function delete(User $user, Withdrawal $withdrawal): bool
    {
        return $this->isOwner($user, $withdrawal)
            && ! $withdrawal->status->isFinal()
            && $withdrawal->processed_at === null;
    }

    /** Approuver, rejeter, marquer payé : administration uniquement. */
    public function process(User $user, Withdrawal $withdrawal): bool
    {
        return false;
    }

    private function isOrganizer(User $user): bool
    {
        return $user->hasRole('organizer-manager') && $user->organizer !== null;
    }

    private function isOwner(User $user, Withdrawal $withdrawal): bool
    {
        return $user->organizer !== null
            && (string) $withdrawal->organizer_id === (string) $user->organizer->id;
    }
}
