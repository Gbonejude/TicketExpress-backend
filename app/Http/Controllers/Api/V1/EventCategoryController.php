<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\V1\EventCategory\CreateEventCategoryAction;
use App\Actions\V1\EventCategory\DeleteEventCategoryAction;
use App\Actions\V1\EventCategory\UpdateEventCategoryAction;
use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\EventCategory\StoreEventCategoryRequest;
use App\Http\Requests\V1\EventCategory\UpdateEventCategoryRequest;
use App\Http\Resources\V1\EventCategoryResource;
use App\Models\EventCategory;
use App\Support\CatalogueCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Event Categories
 *
 * APIs for managing event categories
 */
final class EventCategoryController extends Controller
{
    /**
     * List Event Categories
     *
     * Get a listing of the event categories.
     *
     * @header Accept-Language en
     *
     * @apiResourceCollection \App\Http\Resources\V1\EventCategoryResource
     *
     * @apiResourceModel \App\Models\EventCategory
     */
    public function index(Request $request): JsonResponse
    {
        $payload = CatalogueCache::remember(
            'categories',
            $request,
            CatalogueCache::CATEGORIES_TTL,
            static function () use ($request): array {
                $query = EventCategory::query()
                    ->withCount('events')
                    // What the public tiles show. `events_count` includes events
                    // that have already happened, so a tile advertising 25 led
                    // to a list of 23 — the catalogue only offers upcoming ones.
                    ->withCount(['events as upcoming_events_count' => function (Builder $q): void {
                        $q->where('status', EventStatus::PUBLISHED)
                            ->where(function (Builder $dates): void {
                                $dates->where('end_date', '>=', now())->orWhereNull('end_date');
                            });
                    }])
                    ->orderBy('name');

                if ($request->filled('search')) {
                    $search = (string) $request->input('search');

                    $query->where(function (Builder $q) use ($search): void {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    });
                }

                $perPage = (int) $request->input('per_page', 50);

                return EventCategoryResource::collection($query->paginate(max(1, min($perPage, 100))))
                    ->response()
                    ->getData(true);
            },
        );

        // Handed back raw: `NormalizeApiResponse` wraps it into the standard
        // envelope exactly as it does for a live resource collection, so a
        // cached response is byte-for-byte the same as an uncached one.
        return response()->json($payload);
    }

    /**
     * Store Event Category
     *
     * Store a newly created resource in storage.
     *
     * @header Accept-Language en
     *
     * @response 201 scenario="Created" {
     *   "message": "Event category created successfully",
     *   "data": {
     *     "id": "01jkp5zz...",
     *     "name": "Music",
     *     "slug": "music"
     *   }
     * }
     */
    public function store(StoreEventCategoryRequest $request, CreateEventCategoryAction $action): JsonResponse
    {
        /** @var array{name: string, slug: string} $data */
        $data = $request->validated();

        $category = $action->execute($data);

        return $this->created(new EventCategoryResource($category));
    }

    /**
     * Show Event Category
     *
     * Show the specified resource.
     *
     * @header Accept-Language en
     *
     * @urlParam eventCategory string required The ID of the event category (ULID)
     *
     * @apiResource \App\Http\Resources\V1\EventCategoryResource
     *
     * @apiResourceModel \App\Models\EventCategory
     */
    public function show(EventCategory $id): JsonResponse
    {
        $id->loadCount('events');

        return $this->success(new EventCategoryResource($id));
    }

    /**
     * Update Event Category
     *
     * Update the specified resource in storage.
     *
     * @header Accept-Language en
     *
     * @urlParam eventCategory string required The ID of the event category (ULID)
     *
     * @response 200 scenario="Updated" {
     *   "message": "Event category updated successfully",
     *   "data": {
     *     "id": "01jkp5zz...",
     *     "name": "Music Updated"
     *   }
     * }
     */
    public function update(UpdateEventCategoryRequest $request, EventCategory $id, UpdateEventCategoryAction $action): JsonResponse
    {
        /** @var array{eventCategory: EventCategory, name?: string, slug?: string} $data */
        $data = [
            'eventCategory' => $id,
            ...$request->validated(),
        ];

        $category = $action->execute($data);

        return $this->success(new EventCategoryResource($category));
    }

    /**
     * Delete Event Category
     *
     * Delete the specified resource from storage.
     *
     * @header Accept-Language en
     *
     * @urlParam eventCategory string required The ID of the event category (ULID)
     *
     * @response 204 scenario="Deleted"
     */
    public function destroy(EventCategory $id, DeleteEventCategoryAction $action): JsonResponse
    {
        $action->execute(['eventCategory' => $id]);

        return $this->noContent();
    }
}
