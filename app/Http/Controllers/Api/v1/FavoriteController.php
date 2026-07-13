<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\EventResource;
use App\Models\Event;
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
            ->with(['category', 'venue', 'organizer'])
            ->withCount(['ticketTypes', 'reviews', 'favoritedBy'])
            ->latest('event_favorites.created_at')
            ->paginate(15);

        return EventResource::collection($events);
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
