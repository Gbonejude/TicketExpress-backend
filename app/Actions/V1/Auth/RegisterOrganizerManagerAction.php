<?php

declare(strict_types=1);

namespace App\Actions\V1\Auth;

use App\Actions\Contracts\Action;
use App\Events\ResourceChangedEvent;
use App\Models\Organizer;
use App\Models\User;
use App\Notifications\OrganizerApplicationReceivedNotification;
use App\Notifications\OrganizerRegisteredNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

final class RegisterOrganizerManagerAction implements Action
{
    /**
     * @param  array{
     *     first_name: string,
     *     last_name: string,
     *     email: string,
     *     phone: string,
     *     password: string,
     *     company_name: string,
     *     description?: string|null,
     *     website?: string|null,
     *     logo?: UploadedFile|null,
     * }  $data
     */
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            // Créer User avec role organizer-manager
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole('organizer-manager');

            // Créer Organizer lié
            $organizer = Organizer::create([
                'user_id' => $user->id,
                'company_name' => $data['company_name'],
                'description' => $data['description'] ?? null,
                'website' => $data['website'] ?? null,
                'status' => 'pending', // En attente de validation admin
            ]);

            // Logo fourni avec la demande : c'est ce que l'administrateur voit
            // en examinant le dossier, et ce qui habille la page publique de
            // l'organisateur une fois approuvé.
            if (($data['logo'] ?? null) instanceof UploadedFile) {
                $organizer->addMedia($data['logo'])->toMediaCollection('organizers');
            }

            // Prévenir les administrateurs (cloche + e-mail). `admin` et
            // `super-admin` : une plateforme qui n'a qu'un super-admin ne doit
            // pas laisser la demande sans destinataire.
            //
            // Les rôles sont d'abord résolus depuis la base : `User::role()`
            // lève `RoleDoesNotExist` pour un rôle absent, et une inscription
            // ne doit pas répondre 500 parce qu'un rôle n'a pas été semé.
            $roles = Role::whereIn('name', ['admin', 'super-admin'])->pluck('name')->all();

            $admins = $roles === [] ? collect() : User::role($roles)->get();

            if ($admins->isNotEmpty()) {
                Notification::send(
                    $admins,
                    new OrganizerRegisteredNotification($organizer, $user)
                );
            }

            // Accuser réception au demandeur : sans cela l'inscription se
            // termine en silence sur un compte qui ne peut encore rien faire.
            $user->notify(new OrganizerApplicationReceivedNotification($organizer));

            // Real-time signal so the back-office organizers list refreshes
            // (with a toast) without a manual reload.
            DB::afterCommit(fn () => ResourceChangedEvent::dispatch(
                'organizers',
                'created',
                $organizer->id,
                $organizer->company_name,
            ));

            return $user->load('organizer');
        });
    }
}
