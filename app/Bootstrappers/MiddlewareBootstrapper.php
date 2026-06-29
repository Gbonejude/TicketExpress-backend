<?php

declare(strict_types=1);

namespace App\Bootstrappers;

use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\Localize;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

final class MiddlewareBootstrapper
{
    public function __invoke(Middleware $middleware): void
    {
        $middleware->alias([
            'verified' => EnsureEmailIsVerified::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        $middleware->api(prepend: [
            Localize::class,
        ]);
    }
}
