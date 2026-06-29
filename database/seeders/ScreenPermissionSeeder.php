<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Screen;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds the back-office screen permissions (`screen.*`) and wires them to the
 * default privileged roles.
 *
 * - super-admin : flagged back-office. No explicit permissions needed — the
 *   Gate::before bypass grants it everything (AppServiceProvider).
 * - admin       : flagged back-office and granted every screen EXCEPT the
 *   super-admin-only ones (managing admins/roles).
 *
 * Must run AFTER the role seeders (roles must already exist).
 */
final class ScreenPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. One permission per screen (guard `web`, matching the roles).
        foreach (Screen::permissions() as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        // 2. super-admin — back-office, full access via Gate::before.
        Role::query()
            ->where('name', 'super-admin')
            ->first()
            ?->forceFill([
                'is_back_office' => true,
                'label' => 'Super administrateur',
            ])
            ->save();

        // 3. admin — back-office, every screen except the super-admin-only ones.
        $admin = Role::query()->where('name', 'admin')->first();
        if ($admin !== null) {
            $admin->forceFill([
                'is_back_office' => true,
                'label' => 'Administration',
            ])->save();

            $admin->syncPermissions(
                array_map(
                    static fn (Screen $s): string => $s->permission(),
                    Screen::defaultAdminScreens(),
                ),
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
