<?php

declare(strict_types=1);

namespace App\Bootstrappers;

use App\Exceptions\ApiException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;

final class ExceptionsBootstrapper
{
    public function __invoke(Exceptions $exceptions): void
    {
        $exceptions->render(function (ApiException $e, Request $request) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->statusCode);
        });
    }
}
