<?php

declare(strict_types=1);

namespace App\Actions\V1\User;

use App\Actions\Contracts\Action;
use App\Jobs\SendEmailJob;
use App\Mail\AdminCredentialsMail;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class CreateUserAction implements Action
{
    /**
     * Crée un utilisateur et lui envoie ses identifiants.
     *
     * Le mot de passe est tiré ici plutôt que saisi dans le back-office : un
     * administrateur qui choisit le mot de passe d'un autre le connaît, et le
     * transmet ensuite par un canal quelconque. Généré puis envoyé au seul
     * destinataire, il ne passe par personne.
     *
     * Le cast `hashed` du modèle fait le hachage ; la valeur en clair ne sert
     * qu'au mail.
     *
     * @param  array{
     *     last_name: string,
     *     first_name: string,
     *     phone: string,
     *     gender: string,
     *     role?: string,
     *     email?: string|null,
     *     address?: string|null,
     *     birthday?: string|null,
     *     organization_id?: string|null,
     *     image?: UploadedFile|null,
     * }  $data
     */
    public function execute(array $data): mixed
    {
        $password = Str::password(12);

        $user = User::create([
            ...collect($data)->except(['image', 'role', 'password'])->toArray(),
            'password' => $password,
        ]);

        if (isset($data['role'])) {
            $user->assignRole($data['role']);
        }

        if (($data['image'] ?? null) instanceof UploadedFile) {
            $user->addMedia($data['image'])
                ->toMediaCollection(collectionName: 'users');
        }

        // La garde sur l'adresse couvre les appels internes (seeder, tinker) :
        // `StoreRequest` la rend obligatoire côté API.
        if ($user->email !== null && $user->email !== '') {
            SendEmailJob::dispatch($user->email, AdminCredentialsMail::class, [$user, $password]);
        }

        return $user;
    }
}
