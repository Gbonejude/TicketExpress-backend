<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\V1\Organizer\CreateOrganizerAction;
use App\Actions\V1\Organizer\DeleteOrganizerAction;
use App\Actions\V1\Organizer\UpdateOrganizerAction;
use App\Enums\OrganizerStatus;
use App\Events\ResourceChangedEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Organizer\StoreOrganizerRequest;
use App\Http\Requests\V1\Organizer\UpdateOrganizerRequest;
use App\Http\Resources\V1\OrganizerResource;
use App\Models\Organizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;

/**
 * @group Organizers
 *
 * APIs for managing event organizers
 */
final class OrganizerController extends Controller
{
    /**
     * List Organizers
     *
     * Get a listing of the organizers.
     *
     * @header Accept-Language en
     *
     * @apiResourceCollection \App\Http\Resources\V1\OrganizerResource
     *
     * @apiResourceModel \App\Models\Organizer
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Organizer::query()
            ->with('user')
            ->withCount('events')
            ->latest();

        if ($request->filled('search')) {
            $search = (string) $request->input('search');

            $query->where(function (Builder $q) use ($search): void {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('website', 'like', "%{$search}%");
            });
        }

        $organizers = $query->paginate(15);

        return OrganizerResource::collection($organizers);
    }

    /**
     * Store Organizer
     *
     * Store a newly created resource in storage.
     *
     * @header Accept-Language en
     *
     * @response 201 scenario="Created" {
     *   "message": "Organizer created successfully",
     *   "data": {
     *     "id": "01jkp5zz...",
     *     "userId": "01jkp5yy...",
     *     "companyName": "EventPro Inc"
     *   }
     * }
     */
    public function store(StoreOrganizerRequest $request, CreateOrganizerAction $action): JsonResponse
    {
        /** @var array{user_id: string, company_name: string, description?: string|null, website?: string|null, status?: string, logo?: UploadedFile|null} $data */
        $data = $request->validated();

        $organizer = $action->execute($data);

        return $this->created(new OrganizerResource($organizer));
    }

    /**
     * Show Organizer
     *
     * Show the specified resource.
     *
     * @header Accept-Language en
     *
     * @urlParam organizer string required The ID of the organizer (ULID)
     *
     * @apiResource \App\Http\Resources\V1\OrganizerResource
     *
     * @apiResourceModel \App\Models\Organizer
     */
    public function show(Organizer $id): JsonResponse
    {
        $id->load('user')->loadCount('events');

        return $this->success(new OrganizerResource($id));
    }

    /**
     * Update Organizer
     *
     * Update the specified resource in storage.
     *
     * @header Accept-Language en
     *
     * @urlParam organizer string required The ID of the organizer (ULID)
     *
     * @response 200 scenario="Updated" {
     *   "message": "Organizer updated successfully",
     *   "data": {
     *     "id": "01jkp5zz...",
     *     "companyName": "EventPro Inc Updated"
     *   }
     * }
     */
    public function update(UpdateOrganizerRequest $request, Organizer $id, UpdateOrganizerAction $action): JsonResponse
    {
        /** @var array{organizer: Organizer, user_id?: string, company_name?: string, description?: string|null, website?: string|null, status?: string, logo?: UploadedFile|null} $data */
        $data = [
            'organizer' => $id,
            ...$request->validated(),
        ];

        $organizer = $action->execute($data);

        ResourceChangedEvent::dispatch('organizers', 'updated', $organizer->id, $organizer->company_name);

        return $this->success(new OrganizerResource($organizer));
    }

    /**
     * Delete Organizer
     *
     * Delete the specified resource from storage.
     *
     * @header Accept-Language en
     *
     * @urlParam organizer string required The ID of the organizer (ULID)
     *
     * @response 204 scenario="Deleted"
     */
    public function destroy(Organizer $id, DeleteOrganizerAction $action): JsonResponse
    {
        $organizerId = $id->id;
        $action->execute(['organizer' => $id]);

        ResourceChangedEvent::dispatch('organizers', 'deleted', $organizerId);

        return $this->noContent();
    }

    /**
     * Approve Organizer
     *
     * Approves a pending organizer registration. Clears any previous rejection
     * reason.
     *
     * @urlParam organizer string required The ID of the organizer (ULID)
     */
    public function approve(Organizer $id): JsonResponse
    {
        $id->update([
            'status' => OrganizerStatus::APPROVED,
            'rejection_reason' => null,
        ]);

        ResourceChangedEvent::dispatch('organizers', 'updated', $id->id, $id->company_name);

        return $this->success(new OrganizerResource($id->load('user')->loadCount('events')));
    }

    /**
     * Reject Organizer
     *
     * Rejects an organizer registration with an optional reason shown to the
     * organizer.
     *
     * @urlParam organizer string required The ID of the organizer (ULID)
     *
     * @bodyParam reason string The reason for the rejection. Example: Documents manquants.
     */
    public function reject(Request $request, Organizer $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $id->update([
            'status' => OrganizerStatus::REJECTED,
            'rejection_reason' => $validated['reason'] ?? null,
        ]);

        ResourceChangedEvent::dispatch('organizers', 'updated', $id->id, $id->company_name);

        return $this->success(new OrganizerResource($id->load('user')->loadCount('events')));
    }

    /**
     * Activate Organizer
     *
     * Re-activates a deactivated organizer. Their events become visible on the
     * client side again.
     *
     * @urlParam organizer string required The ID of the organizer (ULID)
     */
    public function activate(Organizer $id): JsonResponse
    {
        $id->update(['is_active' => true]);

        ResourceChangedEvent::dispatch('organizers', 'updated', $id->id, $id->company_name);

        return $this->success(new OrganizerResource($id->load('user')->loadCount('events')));
    }

    /**
     * Deactivate Organizer
     *
     * Deactivates an organizer. Their data is kept but their events are hidden
     * from the client side until re-activated.
     *
     * @urlParam organizer string required The ID of the organizer (ULID)
     */
    public function deactivate(Organizer $id): JsonResponse
    {
        $id->update(['is_active' => false]);

        ResourceChangedEvent::dispatch('organizers', 'updated', $id->id, $id->company_name);

        return $this->success(new OrganizerResource($id->load('user')->loadCount('events')));
    }
}
