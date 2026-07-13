<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\V1\Venue\CreateVenueAction;
use App\Actions\V1\Venue\DeleteVenueAction;
use App\Actions\V1\Venue\UpdateVenueAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Venue\StoreVenueRequest;
use App\Http\Requests\V1\Venue\UpdateVenueRequest;
use App\Http\Resources\V1\VenueResource;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Venues
 *
 * APIs for managing event venues
 */
final class VenueController extends Controller
{
    /**
     * List Venues
     *
     * Get a listing of the venues.
     *
     * @header Accept-Language en
     *
     * @apiResourceCollection \App\Http\Resources\V1\VenueResource
     *
     * @apiResourceModel \App\Models\Venue
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Venue::query()
            ->withCount('events')
            ->latest();

        if ($request->filled('search')) {
            $search = (string) $request->input('search');

            $query->where(function (Builder $q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $venues = $query->paginate(15);

        return VenueResource::collection($venues);
    }

    /**
     * Store Venue
     *
     * Store a newly created resource in storage.
     *
     * @header Accept-Language en
     *
     * @response 201 scenario="Created" {
     *   "message": "Venue created successfully",
     *   "data": {
     *     "id": "01jkp5zz...",
     *     "name": "Grand Arena",
     *     "address": "123 Main Street",
     *     "city": "Lomé"
     *   }
     * }
     */
    public function store(StoreVenueRequest $request, CreateVenueAction $action): JsonResponse
    {
        /** @var array{name: string, address: string, city: string, country?: string|null, capacity?: int|null, latitude?: string|null, longitude?: string|null} $data */
        $data = $request->validated();

        $venue = $action->execute($data);

        return $this->created(new VenueResource($venue));
    }

    /**
     * Show Venue
     *
     * Show the specified resource.
     *
     * @header Accept-Language en
     *
     * @urlParam venue string required The ID of the venue (ULID)
     *
     * @apiResource \App\Http\Resources\V1\VenueResource
     *
     * @apiResourceModel \App\Models\Venue
     */
    public function show(Venue $id): JsonResponse
    {
        $id->loadCount('events');

        return $this->success(new VenueResource($id));
    }

    /**
     * Update Venue
     *
     * Update the specified resource in storage.
     *
     * @header Accept-Language en
     *
     * @urlParam venue string required The ID of the venue (ULID)
     *
     * @response 200 scenario="Updated" {
     *   "message": "Venue updated successfully",
     *   "data": {
     *     "id": "01jkp5zz...",
     *     "name": "Grand Arena Updated"
     *   }
     * }
     */
    public function update(UpdateVenueRequest $request, Venue $id, UpdateVenueAction $action): JsonResponse
    {
        /** @var array{name?: string, address?: string, city?: string, country?: string, capacity?: int|null, latitude?: string|null, longitude?: string|null} $validated */
        $validated = $request->validated();

        /** @var array{model: Venue, name?: string, address?: string, city?: string, country?: string, capacity?: int|null, latitude?: string|null, longitude?: string|null} $data */
        $data = [
            'model' => $id,
            ...$validated,
        ];

        $venue = $action->execute($data);

        return $this->success(new VenueResource($venue));
    }

    /**
     * Delete Venue
     *
     * Delete the specified resource from storage.
     *
     * @header Accept-Language en
     *
     * @urlParam venue string required The ID of the venue (ULID)
     *
     * @response 204 scenario="Deleted"
     */
    public function destroy(Venue $id, DeleteVenueAction $action): JsonResponse
    {
        $action->execute(['model' => $id]);

        return $this->noContent();
    }
}
