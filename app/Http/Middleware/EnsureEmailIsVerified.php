<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if (
            $user instanceof MustVerifyEmail &&
            ! $user->hasVerifiedEmail()
        ) {
            return new JsonResponse([
                'message' => 'Votre adresse email doit être vérifiée.',
            ], 403);
        }

        return $next($request);
    }
}
