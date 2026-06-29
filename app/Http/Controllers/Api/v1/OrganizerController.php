<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\V1\Organizer\CreateOrganizerAction;
use App\Actions\V1\Organizer\DeleteOrganizerAction;
use App\Actions\V1\Organizer\UpdateOrganizerAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Organizer\StoreOrganizerRequest;
use App\Http\Requests\V1\Organizer\UpdateOrganizerRequest;
use App\Http\Resources\V1\OrganizerResource;
use App\Models\Organizer;
use Illuminate\Http\JsonResponse;
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
    public function index(): JsonResponse
    {
        $organizers = Organizer::query()
            ->with('user')
            ->withCount('events')
            ->latest()
            ->get();

        return $this->success(OrganizerResource::collection($organizers));
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
        $action->execute(['organizer' => $id]);

        return $this->noContent();
    }
}
