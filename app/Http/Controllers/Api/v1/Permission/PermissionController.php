<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Permission;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Permission\UpdatePermissionRequest;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

/**
 * @group Permission management
 *
 * APIs for managing permissions (super-admin only)
 */
final class PermissionController extends Controller
{
    /**
     * List Permissions
     *
     * Get a listing of all permissions.
     *
     * @header Accept-Language en
     *
     * @response 200 scenario="Success" {
     *   "success": true,
     *   "message": "Success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "screen.dashboard",
     *       "guard_name": "web"
     *     }
     *   ]
     * }
     */
    public function index(): JsonResponse
    {
        $permissions = Permission::query()->orderBy('name')->get();

        return $this->success($permissions);
    }

    /**
     * Paginate Permissions
     *
     * Get a paginated listing of permissions.
     *
     * @header Accept-Language en
     *
     * @queryParam page integer Page number. Example: 1
     * @queryParam per_page integer Items per page. Example: 15
     *
     * @response 200 scenario="Success" {
     *   "success": true,
     *   "message": "Success",
     *   "data": {
     *     "current_page": 1,
     *     "data": [
     *       {
     *         "id": 1,
     *         "name": "screen.dashboard",
     *         "guard_name": "web"
     *       }
     *     ],
     *     "total": 15
     *   }
     * }
     */
    public function paginate(): JsonResponse
    {
        $permissions = Permission::query()
            ->orderBy('name')
            ->paginate(request()->input('per_page', 15));

        return $this->success($permissions);
    }

    /**
     * Show Permission
     *
     * Get a specific permission by ID.
     *
     * @header Accept-Language en
     *
     * @urlParam id integer required The ID of the permission. Example: 1
     *
     * @response 200 scenario="Success" {
     *   "success": true,
     *   "message": "Success",
     *   "data": {
     *     "id": 1,
     *     "name": "screen.dashboard",
     *     "guard_name": "web"
     *   }
     * }
     */
    public function show(int $id): JsonResponse
    {
        $permission = Permission::findOrFail($id);

        return $this->success($permission);
    }

    /**
     * Store Permission
     *
     * Create a new permission.
     *
     * @header Accept-Language en
     *
     * @bodyParam name string required The permission name. Example: screen.new_feature
     * @bodyParam guard_name string The guard name. Defaults to 'web'. Example: web
     *
     * @response 201 scenario="Created" {
     *   "success": true,
     *   "message": "Permission created successfully",
     *   "data": {
     *     "id": 16,
     *     "name": "screen.new_feature",
     *     "guard_name": "web"
     *   }
     * }
     */
    public function store(): JsonResponse
    {
        $validated = request()->validate([
            'name' => 'required|string|unique:permissions,name',
            'guard_name' => 'sometimes|string',
        ]);

        $permission = Permission::create([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'] ?? 'web',
        ]);

        return $this->created($permission);
    }

    /**
     * Update Permission
     *
     * Update an existing permission.
     *
     * @header Accept-Language en
     *
     * @urlParam id integer required The ID of the permission. Example: 1
     *
     * @bodyParam name string The permission name. Example: screen.updated_feature
     * @bodyParam guard_name string The guard name. Example: web
     *
     * @response 200 scenario="Updated" {
     *   "success": true,
     *   "message": "Permission updated successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "screen.updated_feature",
     *     "guard_name": "web"
     *   }
     * }
     */
    public function update(UpdatePermissionRequest $request, int $id): JsonResponse
    {
        $permission = Permission::findOrFail($id);
        $permission->update($request->validated());

        return $this->success($permission);
    }

    /**
     * Delete Permission
     *
     * Delete a permission.
     *
     * @header Accept-Language en
     *
     * @urlParam id integer required The ID of the permission. Example: 1
     *
     * @response 204 scenario="Deleted"
     */
    public function destroy(int $id): JsonResponse
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return $this->noContent();
    }
}
