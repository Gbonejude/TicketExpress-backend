<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Supprime les dossiers média que plus aucune ligne `media` ne référence.
 *
 * Ils s'accumulent parce que `migrate:fresh` vide la table `media` mais laisse
 * les fichiers sur le disque, et les identifiants repartent ensuite de 1 : le
 * seeding suivant écrit donc ses nouveaux fichiers *dans les anciens dossiers*,
 * qui finissent par contenir deux images pour un seul média — et l'événement
 * peut servir celle de la génération précédente.
 *
 * À lancer après un `migrate:fresh --seed`, ou avant pour repartir propre.
 */
final class PruneOrphanMediaCommand extends Command
{
    protected $signature = 'media:prune-orphans
                            {--disk=public : Le disque à nettoyer}
                            {--force : Supprimer réellement ; sans cette option la commande ne fait que rapporter}';

    protected $description = 'Supprimer les dossiers média orphelins laissés par un migrate:fresh.';

    public function handle(): int
    {
        $disk = Storage::disk((string) $this->option('disk'));
        $known = Media::query()->pluck('id')->map(fn (mixed $id): string => (string) $id)->all();

        $orphans = [];
        $files = 0;

        foreach ($disk->directories() as $directory) {
            $name = basename($directory);

            // Media Library nomme ses dossiers d'après l'id du média. Tout ce qui
            // n'est pas un entier appartient à autre chose et n'est pas touché.
            if (! ctype_digit($name) || in_array($name, $known, true)) {
                continue;
            }

            $orphans[] = $directory;
            $files += count($disk->allFiles($directory));
        }

        $this->components->twoColumnDetail('Médias en base', (string) count($known));
        $this->components->twoColumnDetail('Dossiers orphelins', (string) count($orphans));
        $this->components->twoColumnDetail('Fichiers concernés', (string) $files);

        if ($orphans === []) {
            $this->components->info('Rien à supprimer.');

            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            $this->components->warn('Simulation. Relancer avec --force pour supprimer.');

            return self::SUCCESS;
        }

        foreach ($orphans as $directory) {
            $disk->deleteDirectory($directory);
        }

        $this->components->info(sprintf(
            '%d dossier(s) supprimé(s), %d fichier(s) restant(s) sur le disque.',
            count($orphans),
            count($disk->allFiles()),
        ));

        return self::SUCCESS;
    }
}
