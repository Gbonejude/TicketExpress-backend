<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets the request through if the authenticated user is either:
 *   - an admin or super-admin (spatie/permission role), or
 *   - the owner of the organizer identified by the route `{id}` segment
 *     (user.id === organizer.user_id on the related Organizer row).
 *
 * Used to protect operational mutations on an organizer (status update,
 * profile edit) so a random authenticated user cannot modify someone else's
 * organizer profile. Distinct from the `role:admin|super-admin` middleware
 * used on truly admin-only routes (approve/reject organizer, delete) because
 * profile management is meant to be the organizer's own tool.
 */
final class EnsureOrganizerOwnerOrAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return $next($request);
        }

        $organizerId = $request->route('id');

        if (! $organizerId) {
            abort(403);
        }

        // hasOne relation User->Organizer via organizers.user_id
        $ownerId = $user->organizer?->id;

        if ($ownerId === null || $ownerId !== $organizerId) {
            abort(403);
        }

        return $next($request);
    }
}
