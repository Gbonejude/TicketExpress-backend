<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PaymentResource;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Payments
 *
 * Read-only listing of the payments recorded against orders (back-office).
 *
 * @authenticated
 */
final class PaymentController extends Controller
{
    /**
     * List payments
     *
     * @queryParam status string Filter by status (non_paye, paye). Example: paye
     * @queryParam method string Filter by method (stripe, wave, flooz, tmoney, paypal). Example: wave
     * @queryParam search string Match transaction reference or order number. Example: TX-123
     * @queryParam from date Only payments created on/after this date. Example: 2026-01-01
     * @queryParam to date Only payments created on/before this date. Example: 2026-12-31
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Payment::query()
            ->with('order')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('method')) {
            $query->where('method', $request->input('method'));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->input('search');

            $query->where(function (Builder $q) use ($search): void {
                $q->where('transaction_reference', 'like', "%{$search}%")
                    ->orWhereHas('order', function (Builder $o) use ($search): void {
                        $o->where('order_number', 'like', "%{$search}%");
                    });
            });
        }

        return PaymentResource::collection($query->paginate(15));
    }

    /**
     * Show payment
     *
     * @urlParam payment string required The payment ID (ULID).
     */
    public function show(Payment $payment): JsonResponse
    {
        return $this->success(new PaymentResource($payment->load('order')));
    }
}
