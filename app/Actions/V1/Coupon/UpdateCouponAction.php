<?php

declare(strict_types=1);

namespace App\Actions\V1\Coupon;

use App\Actions\Contracts\Action;
use App\Models\Coupon;

final class UpdateCouponAction implements Action
{
    /**
     * @param  array{
     *     coupon: Coupon,
     *     code?: string,
     *     type?: string,
     *     value?: string|int|float,
     *     max_usage?: int|null,
     *     used_count?: int,
     *     start_date?: string|null,
     *     end_date?: string|null,
     *     event_ids?: array<int, string>|null,
     * }  $data
     */
    public function execute(array $data): mixed
    {
        $coupon = $data['coupon'];
        $eventIds = $data['event_ids'] ?? null;

        $coupon->update(collect($data)->except(['coupon', 'event_ids'])->toArray());

        if ($eventIds !== null) {
            $coupon->events()->sync($eventIds);
        }

        return $coupon->fresh();
    }
}
