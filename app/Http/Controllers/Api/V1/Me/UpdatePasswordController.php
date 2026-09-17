<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Me;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Me\UpdatePasswordRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * @group Authentication
 *
 * @subgroup Session
 *
 * @authenticated
 */
final class UpdatePasswordController extends Controller
{
    /**
     * Change my password
     *
     * Exige le mot de passe actuel. Les autres jetons du compte sont révoqués,
     * celui de la session en cours est conservé.
     *
     * @bodyParam current_password string required Example: Password123!
     * @bodyParam password string required Example: NouveauMotDePasse1!
     * @bodyParam password_confirmation string required Example: NouveauMotDePasse1!
     */
    public function __invoke(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update(['password' => Hash::make($request->validated('password'))]);

        $currentId = PersonalAccessToken::findToken((string) $request->bearerToken())?->getKey();

        $user->tokens()
            ->when($currentId !== null, fn (Builder $query) => $query->whereKeyNot($currentId))
            ->delete();

        return $this->success(message: 'Mot de passe modifié.');
    }
}
