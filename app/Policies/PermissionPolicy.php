<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

/**
 * Authorization policy for permission operations.
 *
 * SECURITY P0-4 — Permission management is super-admin-only:
 * - viewAny: super-admin only (screen.administrators required)
 * - view: super-admin only
 * - create: forbidden (permissions are seeded, not user-created)
 * - update: forbidden (permissions are immutable system entities)
 * - delete: forbidden (permissions are immutable system entities)
 *
 * Business rules:
 * - Only super-admins with screen.administrators can view permissions
 * - Permissions are seeded during installation, not created via UI
 * - Permissions are immutable (cannot be edited or deleted)
 * - Super-admin/admin bypass via AdminBypassesAll trait
 */
final class PermissionPolicy
{
    use AdminBypassesAll;

    public function viewAny(User $user): bool
    {
        // Only super-admins with administrators screen can view permissions
        return $user->can('screen.administrators');
    }

    public function view(User $user, Permission $permission): bool
    {
        // Only super-admins with administrators screen can view permission details
        return $user->can('screen.administrators');
    }

    public function create(User $user): bool
    {
        // Permissions are seeded, not created via UI
        return false;
    }

    public function update(User $user, Permission $permission): bool
    {
        // Permissions are immutable system entities
        return false;
    }

    public function delete(User $user, Permission $permission): bool
    {
        // Permissions are immutable system entities
        return false;
    }
}
