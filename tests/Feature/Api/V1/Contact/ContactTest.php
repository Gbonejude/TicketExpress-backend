<?php

declare(strict_types=1);

use App\Mail\ContactMessageMail;
use Illuminate\Support\Facades\Mail;

$payload = [
    'name' => 'Koffi Mensah',
    'email' => 'koffi.mensah@example.com',
    'phone' => '+228 90 12 34 56',
    'subject' => 'Support billetterie',
    'message' => 'Bonjour, je n\'ai pas reçu mon billet par WhatsApp.',
];

it('sends the contact form to the support inbox', function () use ($payload) {
    Mail::fake();
    config(['mail.contact_address' => 'support@ticketexpress.tg']);

    $this->postJson('/api/v1/contact', $payload)
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Message envoyé. Notre équipe vous répondra rapidement.',
        ]);

    Mail::assertQueued(
        ContactMessageMail::class,
        fn (ContactMessageMail $mail): bool => $mail->hasTo('support@ticketexpress.tg')
            && $mail->senderEmail === $payload['email']
            && $mail->body === $payload['message'],
    );
});

it('replies to the sender rather than to the support inbox', function () use ($payload) {
    Mail::fake();

    $this->postJson('/api/v1/contact', $payload)->assertSuccessful();

    Mail::assertQueued(
        ContactMessageMail::class,
        fn (ContactMessageMail $mail): bool => $mail->hasReplyTo($payload['email']),
    );
});

it('rejects an invalid address and sends nothing', function () use ($payload) {
    Mail::fake();

    $this->postJson('/api/v1/contact', [...$payload, 'email' => 'pas-une-adresse'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);

    Mail::assertNothingQueued();
});

it('requires a message of at least ten characters', function () use ($payload) {
    Mail::fake();

    $this->postJson('/api/v1/contact', [...$payload, 'message' => 'court'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['message']);

    Mail::assertNothingQueued();
});

it('accepts a message without a phone number', function () use ($payload) {
    Mail::fake();

    $this->postJson('/api/v1/contact', [...$payload, 'phone' => null])
        ->assertSuccessful();

    Mail::assertQueued(
        ContactMessageMail::class,
        fn (ContactMessageMail $mail): bool => $mail->senderPhone === '',
    );
});
