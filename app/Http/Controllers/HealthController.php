<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class HealthController extends Controller
{
    /**
     * Health check endpoint.
     *
     * Checks database connectivity and returns application status.
     */
    public function check(): JsonResponse
    {
        try {
            DB::connection()->getPdo();
            $dbStatus = 'connected';
        } catch (\Exception $e) {
            $dbStatus = 'disconnected';
        }

        return response()->json([
            'status' => 'ok',
            'service' => config('app.name', 'TicketExpress'),
            'version' => config('app.version', '1.0.0'),
            'database' => $dbStatus,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
