<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

/**
 * Authorization policy for role operations.
 *
 * SECURITY P0-4 — Role management is super-admin-only:
 * - viewAny: super-admin only (screen.administrators required)
 * - view: super-admin only
 * - create: super-admin only (for creating custom back-office roles)
 * - update: super-admin only (for updating role permissions/screens)
 * - delete: super-admin only (protected roles cannot be deleted)
 *
 * Business rules:
 * - Only super-admins with screen.administrators can manage roles
 * - System roles (super-admin, admin, client, organizer-manager) are protected
 * - Custom back-office roles can be created for specific access patterns
 * - Role updates affect screen permissions for all users with that role
 * - Super-admin/admin bypass via AdminBypassesAll trait
 */
final class RolePolicy
{
    use AdminBypassesAll;

    public function viewAny(User $user): bool
    {
        // Only super-admins with administrators screen can view roles
        return $user->can('screen.administrators');
    }

    public function view(User $user, Role $role): bool
    {
        // Only super-admins with administrators screen can view role details
        return $user->can('screen.administrators');
    }

    public function create(User $user): bool
    {
        // Only super-admins can create custom back-office roles
        return $user->can('screen.administrators');
    }

    public function update(User $user, Role $role): bool
    {
        // Only super-admins can update role permissions/screens
        return $user->can('screen.administrators');
    }

    public function delete(User $user, Role $role): bool
    {
        // Only super-admins can delete roles (protected roles are handled in controller)
        return $user->can('screen.administrators');
    }
}
