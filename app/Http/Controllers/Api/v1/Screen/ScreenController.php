<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Screen;

use App\Enums\Screen;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * @group Access Control
 *
 * @subgroup Screen
 *
 * @subgroupDescription The catalogue of back-office screens that can be
 * attached to roles.
 *
 * @authenticated
 */
final class ScreenController extends Controller
{
    /**
     * List screens
     *
     * Returns the full, ordered catalogue of back-office screens (key, label,
     * icon, front route, section, order). Used by the role-management UI to
     * let a super-admin tick which screens a role may access.
     *
     * @header Accept-Language en
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
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
    public function __invoke(): JsonResponse
    {
        return $this->success([
            'screens' => Screen::catalogue(),
        ]);
    }
}
