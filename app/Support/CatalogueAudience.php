<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * À qui l'on montre quoi du catalogue.
 *
 * Trois publics, et non deux : anonyme, participant, exploitation. La distinction
 * qui compte n'est pas « connecté ou pas » — un participant est connecté lui
 * aussi — mais « est-ce que cette personne travaille sur la plateforme ». Le
 * catalogue public ne montre que ce qui est encore à venir et dont
 * l'organisateur est actif ; le back-office voit tout, sinon un organisateur ne
 * pourrait plus consulter ses propres événements passés ni ses recettes.
 *
 * La même réponse sert à deux endroits qui doivent absolument s'accorder : les
 * requêtes des contrôleurs, et la clé de cache du catalogue. Les faire diverger
 * revient à servir à un participant la page mise en cache pour un
 * administrateur — donc à lui montrer précisément ce qu'on voulait cacher.
 *
 * @see CatalogueCache
 */
final class CatalogueAudience
{
    /** Vrai si l'appelant voit le catalogue entier, passé compris. */
    public static function seesEverything(?User $user): bool
    {
        return $user !== null && $user->hasAnyRole(UserRole::staff());
    }

    /** Idem, depuis une requête. */
    public static function requestSeesEverything(Request $request): bool
    {
        return self::seesEverything(self::user($request));
    }

    /**
     * L'organisateur auquel une requête est bornée, ou `null` si elle voit tout.
     *
     * Voir tout le catalogue passé (`seesEverything`) et n'en administrer que sa
     * part sont deux règles distinctes : un organisateur revient bien sur ses
     * propres événements terminés, mais jamais sur ceux d'un confrère. La
     * permission d'écran lui ouvre l'écran, elle ne lui donne pas les données des
     * autres — c'est précisément ce que `screen.events` laissait faire tant que
     * rien ne bornait la requête.
     *
     * L'administration (admin / super-admin) n'est bornée par rien. Un
     * participant non plus n'est pas concerné ici : ses listes se cloisonnent par
     * identité d'acheteur, pas par organisateur.
     *
     * Le sentinelle `__none__` — un identifiant qu'aucun organisateur ne porte —
     * ferme la liste d'un gestionnaire dont le compte n'a pas encore
     * d'organisateur, au lieu de la laisser tout montrer.
     */
    public static function scopedOrganizerId(Request $request): ?string
    {
        $user = self::user($request);

        if ($user === null) {
            return null;
        }

        if ($user->hasAnyRole([UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value])) {
            return null;
        }

        if ($user->hasRole(UserRole::ORGANIZER_MANAGER->value)) {
            return $user->organizer?->id ?? '__none__';
        }

        return null;
    }

    /**
     * L'utilisateur derrière la requête, jeton porteur compris.
     *
     * `$request->user()` ne suffit pas : les routes du catalogue sont publiques,
     * donc sans `auth:sanctum`, et le garde par défaut est `web` — une requête
     * d'API munie de son jeton était donc lue comme anonyme. C'est ce qui rendait
     * la règle « le back-office voit tout » inopérante depuis toujours : elle
     * était écrite, mais jamais vraie.
     *
     * Interroger le garde `sanctum` directement donne l'authentification
     * *facultative* qu'on veut ici : l'identité quand le jeton est là, `null`
     * sinon, et jamais de 401 sur une page publique.
     */
    private static function user(Request $request): ?User
    {
        /** @var User|null $user */
        $user = $request->user() ?? Auth::guard('sanctum')->user();

        return $user;
    }

    /**
     * Le segment qui identifie le public dans une clé de cache.
     *
     * `participant` est distingué de `guest` bien que les deux voient la même
     * chose aujourd'hui : les deux publics n'ont aucune raison de rester
     * identiques, et une clé partagée est le genre de détail qui transforme une
     * évolution anodine en fuite.
     */
    public static function cacheSegment(Request $request): string
    {
        $user = self::user($request);

        if ($user === null) {
            return 'guest';
        }

        if ($user->hasAnyRole([UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value])) {
            return 'staff';
        }

        // Chaque organisateur a sa propre tranche de cache : la liste du
        // catalogue est désormais bornée à ses événements (voir
        // {@see scopedOrganizerId()}), et deux organisateurs qui partageraient la
        // tranche « staff » se seraient servi l'un à l'autre leurs listes.
        if ($user->hasRole(UserRole::ORGANIZER_MANAGER->value)) {
            return 'organizer:'.($user->organizer?->id ?? 'none');
        }

        return 'participant';
    }
}
