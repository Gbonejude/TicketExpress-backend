<?php

declare(strict_types=1);

namespace App\Actions\V1\Coupon;

use App\Actions\Contracts\Action;
use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Support\Facades\Cache;

final class ValidateCouponAction implements Action
{
    /**
     * Validate a coupon code.
     *
     * @param  array{code: string, event_id?: string|null}  $data
     * @return array{valid: bool, coupon: array<string, mixed>}
     */
    public function execute(array $data): array
    {
        $code = $data['code'];
        $eventId = $data['event_id'] ?? null;

        // Cache coupon for 1 hour (3600 seconds)
        // Key includes event_id to handle event-specific validation
        $cacheKey = "coupon:validate:{$code}:".($eventId ?? 'global');

        $coupon = Cache::remember($cacheKey, 3600, function () use ($code) {
            return Coupon::where('code', $code)->first();
        });

        if (! $coupon) {
            throw new \DomainException('Le code promo n\'existe pas.');
        }

        if ($coupon->used_count >= $coupon->max_usage) {
            throw new \DomainException('Ce code promo a atteint sa limite d\'utilisation.');
        }

        if (now()->lt($coupon->start_date) || now()->gt($coupon->end_date)) {
            throw new \DomainException('Ce code promo n\'est pas valide pour cette période.');
        }

        // Check if coupon applies to specific event
        if ($eventId !== null) {
            $coupon->load('events');

            if ($coupon->events->isNotEmpty() && ! $coupon->events->contains('id', $eventId)) {
                throw new \DomainException('Ce code promo ne s\'applique pas à cet événement.');
            }
        }

        /** @var CouponType $couponType */
        $couponType = $coupon->getAttribute('type');

        return [
            'valid' => true,
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'type' => $couponType->value,
                'value' => $coupon->value,
            ],
        ];
    }
}
