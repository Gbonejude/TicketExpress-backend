<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Renomme le rôle « client » en « participant ».
 *
 * Le rôle est renommé en place plutôt que recréé : `model_has_roles` pointe sur
 * l'id du rôle, donc les comptes déjà rattachés le suivent sans qu'on touche à
 * la table pivot. Recréer le rôle aurait détaché tous les acheteurs existants.
 *
 * Le libellé est posé au passage — il était absent, ce qui faisait afficher le
 * nom technique en minuscules dans « Rôles & permissions ».
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->rename(from: 'client', to: 'participant', label: 'Participant');
    }

    public function down(): void
    {
        $this->rename(from: 'participant', to: 'client', label: null);
    }

    /**
     * Renomme le rôle s'il existe, et seulement si la cible est libre.
     *
     * Les deux gardes rendent la migration rejouable et sans danger sur une base
     * déjà à jour, où seul le libellé reste à écrire.
     */
    private function rename(string $from, string $to, ?string $label): void
    {
        $source = DB::table('roles')->where('name', $from)->first();
        $target = DB::table('roles')->where('name', $to)->first();

        if ($source !== null && $target === null) {
            DB::table('roles')
                ->where('id', $source->id)
                ->update(['name' => $to, 'label' => $label]);
        } elseif ($target !== null) {
            DB::table('roles')
                ->where('id', $target->id)
                ->update(['label' => $label]);
        }

        // Spatie met les rôles en cache : sans ça, les vérifications de rôle
        // continueraient de répondre sur l'ancien nom jusqu'à expiration.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
