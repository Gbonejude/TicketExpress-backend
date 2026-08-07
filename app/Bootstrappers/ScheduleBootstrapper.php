<?php

declare(strict_types=1);

namespace App\Bootstrappers;

use Illuminate\Console\Scheduling\Schedule;

/**
 * Tâches récurrentes de la plateforme.
 *
 * Déclarées ici plutôt que dans `routes/console/routes.php` : ce fichier est
 * chargé comme un groupe de routes web (voir RoutingBootstrapper), donc ignoré
 * dès que les routes sont mises en cache — un planning y serait silencieusement
 * perdu en production.
 *
 * Requiert `php artisan schedule:work` en développement, ou en production une
 * entrée cron appelant `schedule:run` chaque minute.
 */
final class ScheduleBootstrapper
{
    public function __invoke(Schedule $schedule): void
    {
        // Un billet reste « valide » jusqu'à ce que le contrôle d'accès de son
        // événement ait fermé. L'heure ronde suffit : personne ne lit un statut
        // à la minute près, et un billet hors fenêtre est de toute façon déjà
        // refusé au portique — l'expiration met le statut d'accord avec le fait.
        $schedule->command('tickets:expire')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground();
    }
}
