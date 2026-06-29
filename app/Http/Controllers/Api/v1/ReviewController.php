<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\V1\Review\CreateReviewAction;
use App\Actions\V1\Review\DeleteReviewAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Review\StoreReviewRequest;
use App\Http\Resources\V1\ReviewResource;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Reviews
 *
 * APIs for managing event reviews
 */
final class ReviewController extends Controller
{
    /**
     * List Reviews
     *
     * Get a listing of the reviews.
     *
     * @header Accept-Language en
     *
     * @queryParam event_id string Filter by event ULID. Example: 01HXE2K3M4N5P6Q7R8S9T0V1W5
     *
     * @apiResourceCollection \App\Http\Resources\V1\ReviewResource
     *
     * @apiResourceModel \App\Models\Review
     */
    public function index(Request $request): JsonResponse
    {
        $query = Review::query()
            ->with(['user', 'event'])
            ->latest();

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->input('event_id'));
        }

        $reviews = $query->get();

        return $this->success(ReviewResource::collection($reviews));
    }

    /**
     * Store Review
     *
     * Store a newly created resource in storage.
     *
     * @header Accept-Language en
     *
     * @response 201 scenario="Created" {
     *   "message": "Review created successfully",
     *   "data": {
     *     "id": "01jkp5zz...",
     *     "eventId": "01jkp5yy...",
     *     "rating": 5,
     *     "comment": "Amazing event!"
     *   }
     * }
     */
    public function store(StoreReviewRequest $request, CreateReviewAction $action, ?string $eventId = null): JsonResponse
    {
        /** @var array{event_id?: string, rating: int, comment?: string|null} $validated */
        $validated = $request->validated();

        // If eventId is in URL, use it; otherwise use event_id from body
        if ($eventId !== null) {
            $validated['event_id'] = $eventId;
        }

        $data = [
            ...$validated,
            'user_id' => $request->user()?->id,
        ];

        /** @var array{event_id: string, user_id: string|null, rating: int, comment?: string|null} $data */
        $review = $action->execute($data);

        return $this->created(new ReviewResource($review));
    }

    /**
     * Show Review
     *
     * Show the specified resource.
     *
     * @header Accept-Language en
     *
     * @urlParam review string required The ID of the review (ULID)
     *
     * @apiResource \App\Http\Resources\V1\ReviewResource
     *
     * @apiResourceModel \App\Models\Review
     */
    public function show(Review $id): JsonResponse
    {
        $id->load(['user', 'event']);

        return $this->success(new ReviewResource($id));
    }

    /**
     * Delete Review
     *
     * Delete the specified resource from storage.
     *
     * @header Accept-Language en
     *
     * @urlParam review string required The ID of the review (ULID)
     *
     * @response 204 scenario="Deleted"
     */
    public function destroy(Review $id, DeleteReviewAction $action): JsonResponse
    {
        $action->execute(['model' => $id]);

        return $this->noContent();
    }
}
