<?php

declare(strict_types=1);

use App\Events\ResourceChangedEvent;
use Illuminate\Contracts\Broadcasting\Broadcaster;
use Illuminate\Support\Facades\Broadcast;

/**
 * `ResourceChangedEvent` n'est qu'un signal « rafraîchis la liste » pour le
 * back-office. Comme il est `ShouldBroadcastNow` (synchrone), une panne du
 * broadcaster (Pusher injoignable : hors-ligne, DNS, clés absentes) lèverait une
 * exception en pleine requête et ferait « planter » une commande ou un paiement
 * pourtant valides. `dispatchQuietly()` doit absorber cet échec.
 */
beforeEach(function (): void {
    // Un broadcaster qui échoue toujours, pour simuler Pusher injoignable.
    Broadcast::extend('boom', fn (): Broadcaster => new class implements Broadcaster
    {
        public function auth($request) {}

        public function validAuthenticationResponse($request, $result) {}

        public function broadcast(array $channels, $event, array $payload = []): void
        {
            throw new RuntimeException('Pusher injoignable');
        }
    });

    config([
        'broadcasting.default' => 'boom',
        'broadcasting.connections.boom' => ['driver' => 'boom'],
    ]);
});

it('propagates the failure on the raw dispatch (control)', function (): void {
    ResourceChangedEvent::dispatch('orders', 'created', 'x');
})->throws(RuntimeException::class);

it('swallows a broadcast failure so the caller is never broken', function (): void {
    ResourceChangedEvent::dispatchQuietly('orders', 'created', 'x');

    // On atteint cette ligne sans exception : c'est tout l'enjeu — un paiement
    // ne doit pas échouer parce que la diffusion temps réel a échoué.
    expect(true)->toBeTrue();
});
