<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Me;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Me\UpdateProfileRequest;
use App\Http\Resources\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;

/**
 * @group Authentication
 *
 * @subgroup Session
 *
 * @authenticated
 */
final class UpdateProfileController extends Controller
{
    /**
     * Update my profile
     *
     * Nom, prénom, e-mail et téléphone du compte connecté.
     *
     * Distinct de `users/{id}`, réservé à l'administration : celle-ci désigne
     * l'utilisateur et peut changer son rôle. Ici l'utilisateur est celui du
     * jeton, et le rôle n'est pas un champ — sans quoi chacun pourrait
     * s'attribuer le sien.
     *
     * @bodyParam first_name string required Example: Komi
     * @bodyParam last_name string required Example: CREPPY
     * @bodyParam email string required Example: komi@example.com
     * @bodyParam phone string Example: 90112233
     */
    public function __invoke(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update($request->safe()->except('image'));

        if ($request->file('image') instanceof UploadedFile) {
            $user->addMedia($request->file('image'))->toMediaCollection('users');
        }

        return $this->success(
            new UserResource($user->fresh()->load('organizer')),
            'Profil mis à jour.',
        );
    }
}
