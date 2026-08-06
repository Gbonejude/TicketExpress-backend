<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * Recherche d'une personne sur plusieurs colonnes, mot par mot.
 *
 * Le motif habituel — `first_name LIKE %terme% OR last_name LIKE %terme%` — ne
 * trouve rien dès qu'on tape un nom complet : « Komi CREPPY » n'est ni dans
 * `first_name` ni dans `last_name`. C'est pourtant ce qu'on tape naturellement.
 *
 * Le terme est donc découpé en mots, et **chaque mot** doit se trouver dans
 * l'une des colonnes. « Komi CREPPY » et « CREPPY Komi » fonctionnent tous les
 * deux, sans dépendre d'une fonction SQL de concaténation — `CONCAT` sur MySQL,
 * `||` sur SQLite : ce qui marche d'un côté casse de l'autre, et les tests
 * tournent sur SQLite.
 *
 * Un mot reste cherché dans toutes les colonnes, donc un email ou un numéro de
 * commande passé en un seul mot continue de fonctionner comme avant.
 */
final class PersonSearch
{
    /**
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     * @param  array<int, string>  $columns
     */
    public static function apply(Builder $query, ?string $term, array $columns): void
    {
        $words = preg_split('/\s+/', trim((string) $term), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($words === [] || $columns === []) {
            return;
        }

        foreach ($words as $word) {
            $query->where(function (Builder $group) use ($word, $columns): void {
                foreach ($columns as $index => $column) {
                    $index === 0
                        ? $group->where($column, 'like', "%{$word}%")
                        : $group->orWhere($column, 'like', "%{$word}%");
                }
            });
        }
    }
}
