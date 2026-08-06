<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trace du traitement d'un retrait.
 *
 * Un retrait déplace de l'argent : savoir qu'il est « payé » sans savoir par qui
 * ni quand rend tout contrôle impossible après coup. `notes` porte le motif —
 * c'est ce qu'on doit pouvoir rendre à un organisateur dont la demande est
 * refusée.
 *
 * `nullOnDelete` sur l'auteur : supprimer un compte d'administration ne doit pas
 * emporter l'historique des paiements.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawals', function (Blueprint $table): void {
            $table->timestamp('processed_at')->nullable()->after('status');
            $table->foreignUlid('processed_by')
                ->nullable()
                ->after('processed_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->text('notes')->nullable()->after('processed_by');
        });
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('processed_by');
            $table->dropColumn(['processed_at', 'notes']);
        });
    }
};
