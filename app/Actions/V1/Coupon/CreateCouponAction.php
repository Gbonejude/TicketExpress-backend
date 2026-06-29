<?php

declare(strict_types=1);

namespace App\Actions\V1\Coupon;

use App\Actions\Contracts\Action;
use App\Models\Coupon;

final class CreateCouponAction implements Action
{
    /**
     * @param  array{
     *     code: string,
     *     type: string,
     *     value: string,
     *     max_usage?: int|null,
     *     used_count?: int,
     *     start_date?: string|null,
     *     end_date?: string|null,
     *     event_ids?: array<int, string>|null,
     * }  $data
     */
    public function execute(array $data): mixed
    {
        $eventIds = $data['event_ids'] ?? null;

        $coupon = Coupon::create(collect($data)->except(['event_ids'])->toArray());

        if (! empty($eventIds)) {
            $coupon->events()->sync($eventIds);
        }

        return $coupon->load('events');
    }
}
