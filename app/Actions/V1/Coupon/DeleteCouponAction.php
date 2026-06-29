<?php

declare(strict_types=1);

namespace App\Actions\V1\Coupon;

use App\Actions\Contracts\Action;
use App\Models\Coupon;

final class DeleteCouponAction implements Action
{
    /**
     * @param  array{coupon: Coupon}  $data
     */
    public function execute(array $data): mixed
    {
        return (bool) $data['coupon']->delete();
    }
}
