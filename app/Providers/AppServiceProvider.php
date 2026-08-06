<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Auth\OtpGenerator;
use App\Contracts\Auth\OtpSender;
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
        ResetPassword::createUrlUsing(fn (object $notifiable, string $token): string => sprintf(
            '%s?token=%s&email=%s',
            rtrim((string) config('app.password_reset_url'), '?'),
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
