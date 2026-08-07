<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Auth\OtpGenerator;
use App\Contracts\Auth\OtpSender;
use App\Enums\UserRole;
use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Repositories\Concerns\OneSignalContract;
use App\Repositories\OneSignalRepository;
use App\Services\Otp\EdokingOtpSender;
use App\Services\Otp\RandomOtpGenerator;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(
            model: PersonalAccessToken::class,
        );

        // Le lien du mail ouvre la page de réinitialisation du front, pas l'API :
        // celle-ci n'a aucune route web, le lien ne menait donc nulle part.
        //
        // Et pas n'importe quel front : le destinataire décide. Un participant
        // était renvoyé vers le back-office, où il n'a pas de compte à ouvrir —
        // il changeait son mot de passe puis se retrouvait devant un écran de
        // connexion qui le refuse. Le rôle est lu sur le notifiable plutôt que
        // pris dans la requête : une URL de redirection choisie par l'appelant
        // est ce qui transforme un mail de réinitialisation en hameçonnage.
        ResetPassword::createUrlUsing(fn (object $notifiable, string $token): string => sprintf(
            '%s?token=%s&email=%s',
            rtrim((string) config($this->passwordResetUrlKey($notifiable)), '?'),
            $token,
            urlencode($notifiable->getEmailForPasswordReset()),
        ));

        JsonResource::withoutWrapping();

        // Gate::before bypass for super-admin role
        // Super-admins have access to everything without explicit permissions
        Gate::before(function (User $user, string $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });
    }

    /**
     * La page de réinitialisation qui correspond au destinataire.
     *
     * Le back-office pour qui y travaille — administration et organisateurs — le
     * site public pour tous les autres. Un compte sans rôle du tout (créé mais
     * pas encore rattaché) est traité comme un participant : c'est le cas le plus
     * probable, et c'est le front sur lequel il pourra effectivement se connecter.
     */
    private function passwordResetUrlKey(object $notifiable): string
    {
        $isStaff = $notifiable instanceof User
            && $notifiable->hasAnyRole(UserRole::staff());

        return $isStaff
            ? 'app.password_reset_url'
            : 'app.participant_password_reset_url';
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ! OTP Sender
        $this->app->bind(
            abstract: OtpSender::class,
            concrete: EdokingOtpSender::class,
        );
        $this->app->bind(
            abstract: OtpGenerator::class,
            concrete: RandomOtpGenerator::class,
        );
        $this->app->bind(
            abstract: OneSignalContract::class,
            concrete: OneSignalRepository::class,
        );
    }
}
