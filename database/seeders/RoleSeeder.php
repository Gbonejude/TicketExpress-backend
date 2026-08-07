<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

final class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Le libellé est écrit à chaque passage, y compris sur un rôle qui existe
     * déjà : sans lui, « Rôles & permissions » retombe sur le nom technique et
     * affiche « organizer-manager » en minuscules. L'enum est la source, ce qui
     * évite de le recopier ici.
     */
    public function run(): void
    {
        foreach (UserRole::cases() as $role) {
            // `findOrCreate` plutôt que `updateOrCreate` : c'est lui qui pose le
            // `guard_name` par défaut, que Spatie ne renseigne que dans sa
            // méthode `create()`.
            Role::findOrCreate($role->value)->update(['label' => $role->label()]);
        }
    }
}
