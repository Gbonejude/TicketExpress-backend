<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Référence du transfert mobile money d'un retrait.
 *
 * Marquer « payé » sans référence, c'est affirmer qu'un virement est parti sans
 * pouvoir le prouver. Cette colonne porte l'identifiant rendu par l'opérateur
 * (Flooz / Mix by Yas) : c'est ce qu'on présente à un organisateur qui dit
 * n'avoir rien reçu, et ce qui permet de rapprocher les comptes.
 *
 * Distincte de `notes`, qui reste le motif libre (raison d'un refus, remarque).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawals', function (Blueprint $table): void {
            $table->string('payout_reference')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table): void {
            $table->dropColumn('payout_reference');
        });
    }
};
