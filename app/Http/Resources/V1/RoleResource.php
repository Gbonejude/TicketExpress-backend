<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Role
 */
final class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $permissionNames = $this->permissions->pluck('name');

        $screens = $permissionNames
            ->filter(static fn (string $name): bool => str_starts_with($name, 'screen.'))
            ->map(static fn (string $name): string => substr($name, 7))
            ->values();

        // Action permissions (e.g. "events.create") — everything that is not a
        // screen-access permission.
        $actions = $permissionNames
            ->reject(static fn (string $name): bool => str_starts_with($name, 'screen.'))
            ->values();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'label' => $this->label ?? $this->name,
            'isBackOffice' => (bool) $this->is_back_office,
            'isSystem' => in_array($this->name, self::systemRoles(), true),
            'screens' => $screens,
            'screensCount' => $screens->count(),
            'actions' => $actions,
            'usersCount' => (int) ($this->users_count ?? 0),
        ];
    }

    /**
     * Roles that ship with the platform and cannot be renamed or deleted.
     *
     * @return array<int, string>
     */
    public static function systemRoles(): array
    {
        return ['super-admin', 'admin', 'organizer-manager', 'participant'];
    }
}
