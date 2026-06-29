<?php

declare(strict_types=1);

namespace App\Actions\V1\User;

use App\Actions\Contracts\Action;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class CreateUserAction implements Action
{
    /**
     * @param  array{
     *     last_name: string,
     *     first_name: string,
     *     phone: string,
     *     gender: string,
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
        $user = User::create(collect($data)->except(['image', 'role'])->toArray());

        if (isset($data['role'])) {
            $user->assignRole($data['role']);
        }

        if (($data['image'] ?? null) instanceof UploadedFile) {
            $user->addMedia($data['image'])
                ->toMediaCollection(collectionName: 'users');
        }

        return $user;
    }
}
