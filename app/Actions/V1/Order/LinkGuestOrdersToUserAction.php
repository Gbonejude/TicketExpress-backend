<?php

declare(strict_types=1);

namespace App\Actions\V1\Order;

use App\Actions\Contracts\Action;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Rattache au compte les commandes passées en invité.
 *
 * Un achat sans connexion enregistre l'e-mail et le téléphone du client mais
 * laisse `user_id` vide. Dès que ce client se connecte ou crée un compte avec
 * les mêmes coordonnées, ses commandes doivent lui revenir — sinon « mes
 * billets » reste vide alors qu'il a bien acheté.
 *
 * Le rapprochement se fait sur l'e-mail OU le téléphone : l'inscription par OTP
 * peut n'avoir que le numéro, et l'achat peut avoir été fait avec l'un ou
 * l'autre. On ne touche que les commandes encore sans compte.
 *
 * @return int le nombre de commandes rattachées
 */
final class LinkGuestOrdersToUserAction implements Action
{
    /**
     * @param  array{user: User}  $data
     */
    public function execute(array $data): int
    {
        $user = $data['user'];

        $email = $user->email !== null && $user->email !== ''
            ? mb_strtolower($user->email)
            : null;

        $phone = $user->phone !== null && $user->phone !== '' ? $user->phone : null;

        if ($email === null && $phone === null) {
            return 0;
        }

        return Order::query()
            ->whereNull('user_id')
            ->where(function (Builder $query) use ($email, $phone): void {
                if ($email !== null) {
                    $query->orWhereRaw('LOWER(email) = ?', [$email]);
                }

                if ($phone !== null) {
                    $query->orWhere('phone', $phone);
                }
            })
            ->update(['user_id' => $user->id]);
    }
}
