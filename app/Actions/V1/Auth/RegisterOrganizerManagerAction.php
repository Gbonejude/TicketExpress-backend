<?php

declare(strict_types=1);

namespace App\Actions\V1\Auth;

use App\Actions\Contracts\Action;
use App\Models\Organizer;
use App\Models\User;
use App\Notifications\OrganizerRegisteredNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

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

            // Notifier les admins via database
            $admins = User::role('admin')->get();
            if ($admins->isNotEmpty()) {
                Notification::send(
                    $admins,
                    new OrganizerRegisteredNotification($organizer, $user)
                );
            }

            return $user->load('organizer');
        });
    }
}
