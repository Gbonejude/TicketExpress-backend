<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Event;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Validation\Validator;

/**
 * Cohérence des fenêtres de dates d'un type de billet : **événement ⊇ vente ⊇
 * promotion**.
 *
 * On ne vend pas après la fin de l'événement, et une promotion ne vit que
 * pendant la vente. La règle « fin après début » reste déclarative sur chaque
 * couple (`sale_end after sale_start`, `promotion_end after promotion_start`) ;
 * cet emboîtement, lui, croise des dates qui vivent en base (l'événement, la
 * vente d'un billet qu'on modifie), d'où sa place ici plutôt qu'en règle.
 *
 * Chaque borne est facultative : on ne contraint que ce qui est renseigné des
 * deux côtés. Une date `null` veut dire « pas de limite », pas « zéro ».
 */
final class TicketDateWindow
{
    /**
     * @param  array{
     *     sale_start?: CarbonInterface|null,
     *     sale_end?: CarbonInterface|null,
     *     promo_start?: CarbonInterface|null,
     *     promo_end?: CarbonInterface|null,
     * }  $dates
     */
    public static function check(Validator $validator, ?Event $event, array $dates): void
    {
        $saleStart = $dates['sale_start'] ?? null;
        $saleEnd = $dates['sale_end'] ?? null;
        $promoStart = $dates['promo_start'] ?? null;
        $promoEnd = $dates['promo_end'] ?? null;

        $eventEnd = $event?->end_date;

        // ── Fenêtres bornées par la fin de l'événement ──────────────────────
        if ($eventEnd !== null) {
            $when = $eventEnd->format('d/m/Y H:i');

            if ($saleStart !== null && $saleStart->gt($eventEnd)) {
                $validator->errors()->add('sale_start_date', "La vente ne peut pas commencer après la fin de l'événement (le $when).");
            }

            if ($saleEnd !== null && $saleEnd->gt($eventEnd)) {
                $validator->errors()->add('sale_end_date', "La vente ne peut pas se terminer après la fin de l'événement (le $when).");
            }

            if ($promoEnd !== null && $promoEnd->gt($eventEnd)) {
                $validator->errors()->add('promotion_end_date', "La promotion ne peut pas se terminer après la fin de l'événement (le $when).");
            }
        }

        // ── Promotion à l'intérieur de la vente ─────────────────────────────
        if ($promoStart !== null && $saleStart !== null && $promoStart->lt($saleStart)) {
            $validator->errors()->add('promotion_start_date', 'La promotion ne peut pas commencer avant la vente.');
        }

        if ($promoEnd !== null && $saleEnd !== null && $promoEnd->gt($saleEnd)) {
            $validator->errors()->add('promotion_end_date', 'La promotion ne peut pas se terminer après la vente.');
        }
    }
}
