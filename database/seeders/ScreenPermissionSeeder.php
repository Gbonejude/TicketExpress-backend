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
        // 1. One permission per screen plus per-screen CRUD actions
        //    (e.g. `screen.events`, `events.create`, `events.update`, …).
        $allPermissions = array_merge(Screen::permissions(), Screen::allActionPermissions());
        foreach ($allPermissions as $name) {
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

            $admin->syncPermissions($this->permissionsFor(Screen::defaultAdminScreens()));
        }

        // 4. organizer-manager — back-office, only their own event/box-office
        //    screens (no user/role/organizer administration).
        $organizer = Role::query()->where('name', 'organizer-manager')->first();
        if ($organizer !== null) {
            $organizer->forceFill([
                'is_back_office' => true,
                'label' => 'Organisateur',
            ])->save();

            $organizer->syncPermissions($this->permissionsFor(Screen::defaultOrganizerScreens()));
        }

        // 5. participant — l'acheteur de billets. Aucun écran de back-office,
        //    mais il lui faut un libellé : sans lui, « Rôles & permissions »
        //    retombe sur le nom technique et affiche « participant » en
        //    minuscules au milieu de libellés soignés.
        Role::query()
            ->where('name', 'participant')
            ->first()
            ?->forceFill([
                'is_back_office' => false,
                'label' => 'Participant',
            ])
            ->save();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Screen-access + all action permissions for the given screens.
     *
     * @param  array<int, Screen>  $screens
     * @return array<int, string>
     */
    private function permissionsFor(array $screens): array
    {
        $permissions = [];

        foreach ($screens as $screen) {
            $permissions[] = $screen->permission();
            $permissions = array_merge($permissions, $screen->actionPermissions());
        }

        return $permissions;
    }
}
