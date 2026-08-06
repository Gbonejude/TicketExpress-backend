<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\V1\Coupon\CreateCouponAction;
use App\Actions\V1\Coupon\DeleteCouponAction;
use App\Actions\V1\Coupon\UpdateCouponAction;
use App\Actions\V1\Coupon\ValidateCouponAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Coupon\StoreCouponRequest;
use App\Http\Requests\V1\Coupon\UpdateCouponRequest;
use App\Http\Resources\V1\CouponResource;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Coupons
 *
 * APIs for managing discount coupons
 */
final class CouponController extends Controller
{
    /**
     * List Coupons
     *
     * Get a listing of the coupons.
     *
     * @header Accept-Language en
     *
     * @apiResourceCollection \App\Http\Resources\V1\CouponResource
     *
     * @apiResourceModel \App\Models\Coupon
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        // Les événements associés accompagnent la liste : la colonne
        // « Événement » les nomme, et le compte seul ne suffisait pas.
        $query = Coupon::query()
            ->with('events')
            ->withCount('events')
            ->latest();

        if ($request->filled('search')) {
            $search = (string) $request->input('search');

            $query->where(function (Builder $q) use ($search): void {
                $q->where('code', 'like', "%{$search}%");
            });
        }

        $coupons = $query->paginate(15);

        return CouponResource::collection($coupons);
    }

    /**
     * Store Coupon
     *
     * Store a newly created resource in storage.
     *
     * @header Accept-Language en
     *
     * @response 201 scenario="Created" {
     *   "message": "Coupon created successfully",
     *   "data": {
     *     "id": "01jkp5zz...",
     *     "code": "SUMMER2024",
     *     "type": "percent",
     *     "value": 20
     *   }
     * }
     */
    public function store(StoreCouponRequest $request, CreateCouponAction $action): JsonResponse
    {
        /** @var array{code: string, type: string, value: string, max_usage?: int|null, start_date?: string|null, end_date?: string|null, event_ids?: array<int, string>|null} $data */
        $data = $request->validated();

        $coupon = $action->execute($data);

        return $this->created(new CouponResource($coupon));
    }

    /**
     * Show Coupon
     *
     * Show the specified resource.
     *
     * @header Accept-Language en
     *
     * @urlParam coupon string required The ID of the coupon (ULID)
     *
     * @apiResource \App\Http\Resources\V1\CouponResource
     *
     * @apiResourceModel \App\Models\Coupon
     */
    public function show(Coupon $id): JsonResponse
    {
        $id->load('events')->loadCount('events');

        return $this->success(new CouponResource($id));
    }

    /**
     * Update Coupon
     *
     * Update the specified resource in storage.
     *
     * @header Accept-Language en
     *
     * @urlParam coupon string required The ID of the coupon (ULID)
     *
     * @response 200 scenario="Updated" {
     *   "message": "Coupon updated successfully",
     *   "data": {
     *     "id": "01jkp5zz...",
     *     "code": "SUMMER2024",
     *     "value": 25
     *   }
     * }
     */
    public function update(UpdateCouponRequest $request, Coupon $id, UpdateCouponAction $action): JsonResponse
    {
        /** @var array{coupon: Coupon, code?: string, type?: string, value?: string, max_usage?: int|null, start_date?: string|null, end_date?: string|null, event_ids?: array<int, string>|null} $data */
        $data = [
            'coupon' => $id,
            ...$request->validated(),
        ];

        $coupon = $action->execute($data);

        return $this->success(new CouponResource($coupon));
    }

    /**
     * Delete Coupon
     *
     * Delete the specified resource from storage.
     *
     * @header Accept-Language en
     *
     * @urlParam coupon string required The ID of the coupon (ULID)
     *
     * @response 204 scenario="Deleted"
     */
    public function destroy(Coupon $id, DeleteCouponAction $action): JsonResponse
    {
        $action->execute(['coupon' => $id]);

        return $this->noContent();
    }

    /**
     * Validate Coupon
     *
     * Validate a coupon code for use.
     *
     * @header Accept-Language en
     *
     * @queryParam code string required The coupon code to validate. Example: SUMMER2024
     * @queryParam event_id string Optional event ULID to check if coupon applies. Example: 01HXE2K3M4N5P6Q7R8S9T0V1W5
     *
     * @response 200 scenario="Valid" {
     *   "message": "Coupon valide.",
     *   "data": {
     *     "valid": true,
     *     "coupon": {
     *       "code": "SUMMER2024",
     *       "type": "percent",
     *       "value": 20
     *     }
     *   }
     * }
     * @response 422 scenario="Invalid" {
     *   "message": "Ce code promo n'est pas valide pour cette période."
     * }
     */
    public function validate(Request $request, ValidateCouponAction $action): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
            'event_id' => ['nullable', 'string'],
        ]);

        try {
            $result = $action->execute([
                'code' => $request->input('code'),
                'event_id' => $request->input('event_id'),
            ]);

            return $this->success(
                data: $result,
                message: 'Coupon valide.',
            );
        } catch (\DomainException $e) {
            return $this->error(
                message: $e->getMessage(),
                status: 422,
            );
        }
    }
}
