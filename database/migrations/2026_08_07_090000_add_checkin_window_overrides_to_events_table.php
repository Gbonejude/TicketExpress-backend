<?php

declare(strict_types=1);

use App\Support\CheckInWindow;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marges de contrôle d'accès propres à un événement.
 *
 * Les réglages plateforme donnent une valeur par défaut raisonnable, mais
 * l'heure d'ouverture des portes appartient à l'événement : un concert ouvre
 * deux heures avant, une formation dès sept heures du matin, un festival six
 * heures avant. Et c'est l'organisateur qui le sait, pas le super-admin.
 *
 * `null` signifie « j'hérite de la plateforme » — d'où deux colonnes nullables
 * plutôt que des valeurs par défaut recopiées : tous les événements existants
 * gardent le comportement actuel, et un changement du réglage global continue
 * de les suivre.
 *
 * Des heures, et non des horaires absolus : une marge suit l'événement quand on
 * en décale la date, là où un « ouverture le 9 août à 16:00 » se désynchronise
 * en silence et n'est jamais recorrigé.
 *
 * @see CheckInWindow
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->decimal('checkin_open_hours_before', 5, 2)->nullable()
                ->after('refund_days_before')
                ->comment('Ouverture du contrôle d\'accès, en heures avant le début (null = réglage plateforme)');

            $table->decimal('checkin_close_hours_after', 5, 2)->nullable()
                ->after('checkin_open_hours_before')
                ->comment('Fermeture du contrôle d\'accès, en heures après la fin (null = réglage plateforme)');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn(['checkin_open_hours_before', 'checkin_close_hours_after']);
        });
    }
};
