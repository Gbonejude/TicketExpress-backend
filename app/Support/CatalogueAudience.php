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

        return self::seesEverything($user) ? 'staff' : 'participant';
    }
}
