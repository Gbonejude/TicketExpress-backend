<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\EventResource;
use App\Models\Event;
use App\Support\CatalogueAudience;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Favorites
 *
 * Authenticated participants can favorite events.
 *
 * @authenticated
 */
final class FavoriteController extends Controller
{
    /**
     * List my favorite events
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $events = $request->user()
            ->favoriteEvents()
            ->with(['category', 'venue', 'organizer', 'ticketTypes'])
            ->withCount(['ticketTypes', 'favoritedBy'])
            ->latest('event_favorites.created_at');

        // Un favori sur un événement terminé n'a plus rien à ouvrir : sa fiche
        // n'est plus servie côté public. La carte restait affichée et menait à
        // une page introuvable. Le rattachement, lui, n'est pas supprimé — ce
        // n'est pas au rendu d'une liste de décider d'effacer les données de
        // quelqu'un.
        if (! CatalogueAudience::requestSeesEverything($request)) {
            $events->where(function (Builder $q): void {
                $q->where('end_date', '>=', now())->orWhereNull('end_date');
            });
        }

        return EventResource::collection($events->paginate(15));
    }

    /**
     * Toggle a favorite
     *
     * Adds the event to the current user's favorites, or removes it if already
     * favorited.
     *
     * @urlParam event string required The event ID (ULID).
     */
    public function toggle(Request $request, Event $event): JsonResponse
    {
        $result = $request->user()->favoriteEvents()->toggle($event->id);

        $favorited = count($result['attached']) > 0;

        return $this->success([
            'favorited' => $favorited,
            'favoritesCount' => $event->favoritedBy()->count(),
        ], $favorited ? 'Ajouté aux favoris.' : 'Retiré des favoris.');
    }
}
