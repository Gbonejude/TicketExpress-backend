<?php

declare(strict_types=1);

namespace App\Actions\V1\Auth;

use App\Actions\Contracts\Action;
use App\Events\Organizer\OrganizerCreatedEvent;
use App\Events\ResourceChangedEvent;
use App\Models\Organizer;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

            // Les notifications (email admin + accusé réception organisateur) sont
            // gérées par OrganizerCreatedListener via l'event — ne pas les envoyer
            // ici aussi, sinon chaque inscription produit un doublon.
            DB::afterCommit(function () use ($organizer): void {
                // Déclenche OrganizerCreatedListener → notif admins + accusé réception
                event(new OrganizerCreatedEvent($organizer));

                // Real-time signal so the back-office organizers list refreshes
                ResourceChangedEvent::dispatchQuietly(
                    'organizers',
                    'created',
                    $organizer->id,
                    $organizer->company_name,
                );
            });

            return $user->load('organizer');
        });
    }
}
