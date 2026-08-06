<?php

declare(strict_types=1);

use App\Enums\OrganizerStatus;
use App\Enums\UserRole;
use App\Mail\OrganizerApprovedMail;
use App\Mail\OrganizerRejectedMail;
use App\Models\Organizer;
use App\Models\User;
use App\Notifications\OrganizerApplicationReceivedNotification;
use App\Notifications\OrganizerRegisteredNotification;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

/**
 * The whole "devenir organisateur" journey, which is entirely e-mail driven:
 * an organizer never signs in on the public site, so the only way they learn
 * where their back-office is, is the approval mail.
 */
beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->seed(ScreenPermissionSeeder::class);

    $this->admin = User::factory()->create(['email' => 'admin@ticketexpress.tg']);
    $this->admin->assignRole(UserRole::ADMIN->value);
    // The approval routes are behind `screen.organizers`, not the role alone.
    $this->admin->givePermissionTo('screen.organizers');
});

function applicationPayload(array $overrides = []): array
{
    return [
        'first_name' => 'Kossi',
        'last_name' => 'Amégan',
        'email' => 'kossi@eventpro.tg',
        'phone' => '+22890112233',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'company_name' => 'EventPro Togo',
        'description' => 'Concerts et festivals à Lomé.',
        ...$overrides,
    ];
}

it('lets an anonymous visitor apply', function (): void {
    Notification::fake();

    $this->postJson('/api/v1/auth/register/organizer-manager', applicationPayload())
        ->assertCreated();

    $this->assertDatabaseHas('organizers', [
        'company_name' => 'EventPro Togo',
        'status' => OrganizerStatus::PENDING->value,
    ]);
});

it('does not sign the applicant in', function (): void {
    Notification::fake();

    // No token: there is nothing for a pending organizer to do on this site.
    $this->postJson('/api/v1/auth/register/organizer-manager', applicationPayload())
        ->assertCreated()
        ->assertJsonMissingPath('data.token');
});

it('notifies the administrators', function (): void {
    Notification::fake();

    $this->postJson('/api/v1/auth/register/organizer-manager', applicationPayload())
        ->assertCreated();

    Notification::assertSentTo($this->admin, OrganizerRegisteredNotification::class);
});

it('sends the administrator an e-mail, not just a bell notification', function (): void {
    Notification::fake();

    $this->postJson('/api/v1/auth/register/organizer-manager', applicationPayload())
        ->assertCreated();

    Notification::assertSentTo(
        $this->admin,
        OrganizerRegisteredNotification::class,
        function (OrganizerRegisteredNotification $notification, array $channels): bool {
            return in_array('mail', $channels, true);
        },
    );
});

it('acknowledges the application to the applicant', function (): void {
    Notification::fake();

    $this->postJson('/api/v1/auth/register/organizer-manager', applicationPayload())
        ->assertCreated();

    $applicant = User::where('email', 'kossi@eventpro.tg')->firstOrFail();

    Notification::assertSentTo($applicant, OrganizerApplicationReceivedNotification::class);
});

it('mails the dashboard link when an administrator approves', function (): void {
    Mail::fake();

    $organizer = Organizer::factory()->create(['status' => OrganizerStatus::PENDING]);

    $this->actingAs($this->admin)
        ->postJson("/api/v1/organizers/{$organizer->id}/approve")
        ->assertOk()
        ->assertJsonPath('data.status', 'approved');

    Mail::assertQueued(OrganizerApprovedMail::class);
});

it('activates the profile on approval', function (): void {
    Mail::fake();

    $organizer = Organizer::factory()->create([
        'status' => OrganizerStatus::PENDING,
        'is_active' => false,
    ]);

    $this->actingAs($this->admin)
        ->postJson("/api/v1/organizers/{$organizer->id}/approve")
        ->assertOk();

    // Approving but leaving the profile off would send them an e-mail
    // inviting them into a back-office that refuses them.
    expect($organizer->fresh()->is_active)->toBeTrue();
});

it('mails the reason when an administrator rejects', function (): void {
    Mail::fake();

    $organizer = Organizer::factory()->create(['status' => OrganizerStatus::PENDING]);

    $this->actingAs($this->admin)
        ->postJson("/api/v1/organizers/{$organizer->id}/reject", ['reason' => 'Dossier incomplet.'])
        ->assertOk()
        ->assertJsonPath('data.rejectionReason', 'Dossier incomplet.');

    Mail::assertQueued(OrganizerRejectedMail::class);
});

it('points the approval mail at the dashboard, not the public site', function (): void {
    config(['app.dashboard_url' => 'https://dashboard.ticketexpress.tg']);
    config(['app.frontend_url' => 'https://ticketexpress.tg']);

    $organizer = Organizer::factory()->create(['status' => OrganizerStatus::APPROVED]);
    $organizer->load('user');

    $rendered = (new OrganizerApprovedMail($organizer))->render();

    expect($rendered)->toContain('https://dashboard.ticketexpress.tg')
        ->and($rendered)->not->toContain('https://ticketexpress.tg/organizer');
});

it('accepts a logo with the application', function (): void {
    Notification::fake();
    Storage::fake('public');

    $this->postJson('/api/v1/auth/register/organizer-manager', applicationPayload([
        'logo' => UploadedFile::fake()->image('logo.png', 400, 400),
    ]))->assertCreated();

    $organizer = Organizer::where('company_name', 'EventPro Togo')->firstOrFail();

    expect($organizer->getFirstMedia('organizers'))->not->toBeNull();
});

it('applies without a logo', function (): void {
    Notification::fake();

    // Optional on purpose: a missing logo must not block an application.
    $this->postJson('/api/v1/auth/register/organizer-manager', applicationPayload())
        ->assertCreated();

    $organizer = Organizer::where('company_name', 'EventPro Togo')->firstOrFail();

    expect($organizer->getFirstMedia('organizers'))->toBeNull();
});

it('refuses a file that is not an image', function (): void {
    Notification::fake();

    $this->postJson('/api/v1/auth/register/organizer-manager', applicationPayload([
        'logo' => UploadedFile::fake()->create('contrat.pdf', 100, 'application/pdf'),
    ]))->assertStatus(422)->assertJsonValidationErrors(['logo']);
});

it('refuses a duplicate e-mail', function (): void {
    Notification::fake();

    $this->postJson('/api/v1/auth/register/organizer-manager', applicationPayload())->assertCreated();

    $this->postJson('/api/v1/auth/register/organizer-manager', applicationPayload())
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});
