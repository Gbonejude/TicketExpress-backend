<?php

declare(strict_types=1);

namespace App\Actions\V1\User;

use App\Actions\Contracts\Action;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class UpdateUserAction implements Action
{
    /**
     * @param  array{
     *     user: User,
     *     last_name?: string,
     *     first_name?: string,
     *     phone?: string,
     *     gender?: string,
     *     role?: string,
     *     email?: string|null,
     *     password?: string|null,
     *     address?: string|null,
     *     birthday?: string|null,
     *     organization_id?: string|null,
     *     image?: UploadedFile|null,
     * }  $data
     */
    public function execute(array $data): mixed
    {
        $user = $data['user'];

        // `password` est écarté explicitement : il n'est plus accepté par
        // `UpdateRequest`, et cette exclusion garantit qu'un appel interne ne
        // puisse pas le réintroduire par la porte de derrière.
        $user->update(collect($data)->except(['user', 'image', 'role', 'password'])->toArray());

        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        if (($data['image'] ?? null) instanceof UploadedFile) {
            $user->addMedia($data['image'])
                ->toMediaCollection(collectionName: 'users');
        }

        return $user->fresh();
    }
}
