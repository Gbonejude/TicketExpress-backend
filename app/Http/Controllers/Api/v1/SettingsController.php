<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Commission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Settings
 *
 * Editable platform settings (super-admin only).
 *
 * @authenticated
 */
final class SettingsController extends Controller
{
    /**
     * Get platform settings
     */
    public function show(): JsonResponse
    {
        return $this->success($this->payload());
    }

    /**
     * Update platform settings
     *
     * @bodyParam commission_rate number required Platform commission as a fraction (0.05 = 5%). Example: 0.05
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:1'],
        ], [
            'commission_rate.max' => 'La commission ne peut pas dépasser 100%.',
        ]);

        Setting::set('commission_rate', $validated['commission_rate']);

        return $this->success($this->payload(), 'Paramètres mis à jour.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'commissionRate' => Commission::rate(),
            'commissionPercent' => round(Commission::rate() * 100, 2),
        ];
    }
}
