<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Role\StoreRoleRequest;
use App\Http\Requests\V1\Role\UpdateRoleRequest;
use App\Http\Resources\V1\RoleResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * @group Access Control
 *
 * @subgroup Roles
 *
 * APIs for managing back-office roles and the screens they can access
 * (super-admin only).
 *
 * @authenticated
 */
final class RoleController extends Controller
{
    /**
     * List roles
     *
     * Returns every role with the screens it is allowed to access and how many
     * users are attached to it.
     */
    public function index(): JsonResponse
    {
        $counts = $this->userCounts();

        $roles = Role::query()
            ->with('permissions')
            ->orderBy('name')
            ->get()
            ->each(fn (Role $role) => $role->setAttribute('users_count', (int) ($counts[$role->getKey()] ?? 0)));

        return $this->success(RoleResource::collection($roles));
    }

    /**
     * Create role
     *
     * Creates a back-office role and grants it the given screens.
     *
     * @bodyParam name string required Machine name (unique). Example: box-office
     * @bodyParam label string A human label. Example: Guichet
     * @bodyParam screens string[] The screen keys the role may access. Example: ["events", "bookings"]
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $data = $request->validated();

        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        $role->forceFill([
            'label' => $data['label'] ?? $data['name'],
            'is_back_office' => true,
        ])->save();

        $role->syncPermissions($this->rolePermissions($data));

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $this->created(new RoleResource($this->withUserCount($role->load('permissions'))));
    }

    /**
     * Show role
     *
     * @urlParam role integer required The role ID.
     */
    public function show(Role $role): JsonResponse
    {
        return $this->success(new RoleResource($this->withUserCount($role->load('permissions'))));
    }

    /**
     * Update role
     *
     * Updates a role's label and/or the screens it can access. The machine name
     * of a role is immutable.
     *
     * @urlParam role integer required The role ID.
     *
     * @bodyParam label string A human label. Example: Guichet
     * @bodyParam screens string[] The screen keys the role may access. Example: ["events", "bookings"]
     */
    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $data = $request->validated();

        if (array_key_exists('label', $data)) {
            $role->forceFill(['label' => $data['label']])->save();
        }

        if (array_key_exists('screens', $data) || array_key_exists('actions', $data)) {
            $role->syncPermissions($this->rolePermissions($data));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $this->success(new RoleResource($this->withUserCount($role->load('permissions'))));
    }

    /**
     * Delete role
     *
     * Deletes a custom role. System roles and roles still attached to users
     * cannot be deleted.
     *
     * @urlParam role integer required The role ID.
     */
    public function destroy(Role $role): JsonResponse
    {
        if (in_array($role->name, RoleResource::systemRoles(), true)) {
            return $this->error('Ce rôle système ne peut pas être supprimé.', 422);
        }

        if ($this->roleUserCount($role) > 0) {
            return $this->error('Ce rôle est encore attribué à des utilisateurs.', 422);
        }

        $role->delete();

        return $this->noContent();
    }

    /**
     * Attaches the users_count attribute (via the pivot table, avoiding the
     * Spatie users() relation which needs a guard-mapped model).
     */
    private function withUserCount(Role $role): Role
    {
        return $role->setAttribute('users_count', $this->roleUserCount($role));
    }

    /**
     * @return \Illuminate\Support\Collection<int|string, int>
     */
    private function userCounts(): \Illuminate\Support\Collection
    {
        return DB::table('model_has_roles')
            ->select('role_id', DB::raw('count(*) as aggregate'))
            ->groupBy('role_id')
            ->pluck('aggregate', 'role_id');
    }

    private function roleUserCount(Role $role): int
    {
        return (int) DB::table('model_has_roles')->where('role_id', $role->getKey())->count();
    }

    /**
     * Maps screen keys to their `screen.*` permission names.
     *
     * @param  array<int, string>  $screens
     * @return array<int, string>
     */
    private function screenPermissions(array $screens): array
    {
        return array_map(static fn (string $key): string => 'screen.'.$key, $screens);
    }

    /**
     * Full permission set for a role: screen access + per-action permissions.
     *
     * @param  array<string, mixed>  $data
     * @return array<int, string>
     */
    private function rolePermissions(array $data): array
    {
        return array_values(array_unique(array_merge(
            $this->screenPermissions($data['screens'] ?? []),
            $data['actions'] ?? [],
        )));
    }
}
