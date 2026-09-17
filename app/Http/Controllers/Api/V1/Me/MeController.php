<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Me;

use App\Enums\Screen;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use App\Support\AbilityRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Authentication
 *
 * @subgroup Session
 *
 * @authenticated
 */
final class MeController extends Controller
{
    /**
     * Current user
     *
     * Returns the authenticated user together with the back-office screens
     * they are allowed to see. The front-end builds its sidebar from
     * `data.screens` and refetches this endpoint when notified in real time
     * that the user's access changed.
     *
     * A super-admin gets every screen (Gate::before bypass); other roles get
     * only the screens granted to their role (`screen.*` permissions).
     *
     * @header Accept-Language en
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "user": {
     *       "id": "01jkp5zz...",
     *       "email": "user@example.com",
     *       "lastName": "Doe",
     *       "firstName": "John"
     *     },
     *     "screens": [
     *       {
     *         "key": "dashboard",
     *         "label": "Dashboard",
     *         "permission": "screen.dashboard"
     *       }
     *     ]
     *   }
     * }
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->loadMissing('organizer');

        $screens = array_values(array_filter(
            Screen::catalogue(),
            static fn (array $screen): bool => $user->can($screen['permission']),
        ));

        return $this->success([
            'user' => new UserResource($user),
            'screens' => $screens,
            'userAbilityRules' => AbilityRules::for($user),
        ]);
    }
}
