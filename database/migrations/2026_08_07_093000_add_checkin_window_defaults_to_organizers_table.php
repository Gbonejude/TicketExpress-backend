<?php

declare(strict_types=1);

use App\Support\CheckInWindow;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marges de contrôle d'accès par défaut d'un organisateur.
 *
 * C'est l'organisateur qui tient le portique, donc c'est lui qui décide de
 * l'heure d'ouverture — pas la plateforme. La plupart travaillent toujours
 * pareil (« mes événements ouvrent six heures avant »), d'où cette valeur posée
 * une fois sur sa fiche et appliquée à tout ce qu'il programme ; un événement
 * particulier peut ensuite la contredire à sa création.
 *
 * `null` signifie « je n'ai rien dit », et l'on retombe alors sur la valeur
 * d'usine de `config/ticketexpress.php`.
 *
 * @see CheckInWindow
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizers', function (Blueprint $table): void {
            $table->decimal('checkin_open_hours_before', 5, 2)->nullable()
                ->after('rejection_reason')
                ->comment('Ouverture du contrôle d\'accès par défaut, en heures avant le début');

            $table->decimal('checkin_close_hours_after', 5, 2)->nullable()
                ->after('checkin_open_hours_before')
                ->comment('Fermeture du contrôle d\'accès par défaut, en heures après la fin');
        });
    }

    public function down(): void
    {
        Schema::table('organizers', function (Blueprint $table): void {
            $table->dropColumn(['checkin_open_hours_before', 'checkin_close_hours_after']);
        });
    }
};
