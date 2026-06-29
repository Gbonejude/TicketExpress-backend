<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Models\Organizer;
use App\Models\User;
use App\Notifications\OrganizerRegisteredNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class RegisterOrganizerManagerTest extends TestCase
{
    use RefreshDatabase;

    private string $endpoint = '/api/v1/auth/register/organizer-manager';

    #[Test]
    public function it_successfully_registers_an_organizer_manager_with_valid_data(): void
    {
        Notification::fake();

        // Créer les rôles nécessaires
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'organizer-manager', 'guard_name' => 'web']);

        // Créer un admin pour recevoir la notification
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $payload = [
            'first_name' => 'Marie',
            'last_name' => 'Kouassi',
            'email' => 'marie.kouassi@example.com',
            'phone' => '+22890111111',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'company_name' => 'Events Pro Togo',
            'description' => 'Spécialiste des événements corporatifs.',
            'website' => 'https://www.eventsprotogo.com',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'user' => [
                        'id',
                        'firstName',
                        'lastName',
                        'email',
                        'phone',
                    ],
                ],
            ])
            ->assertJsonPath('data.user.firstName', 'Marie')
            ->assertJsonPath('data.user.lastName', 'Kouassi')
            ->assertJsonPath('data.user.email', 'marie.kouassi@example.com')
            ->assertJsonPath('data.user.phone', '+22890111111');

        $this->assertDatabaseHas('users', [
            'email' => 'marie.kouassi@example.com',
            'phone' => '+22890111111',
        ]);

        $user = User::where('email', 'marie.kouassi@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('Password123!', $user->password));
        $this->assertTrue($user->hasRole('organizer-manager'));

        $this->assertDatabaseHas('organizers', [
            'user_id' => $user->id,
            'company_name' => 'Events Pro Togo',
            'description' => 'Spécialiste des événements corporatifs.',
            'website' => 'https://www.eventsprotogo.com',
            'status' => 'pending',
        ]);

        // Vérifier que la notification a été envoyée aux admins
        Notification::assertSentTo(
            [$admin],
            OrganizerRegisteredNotification::class
        );
    }

    /** @test */
    public function it_successfully_registers_an_organizer_manager_with_minimal_data(): void
    {
        Notification::fake();

        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'organizer-manager', 'guard_name' => 'web']);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $payload = [
            'first_name' => 'Pierre',
            'last_name' => 'Mensah',
            'email' => 'pierre.mensah@example.com',
            'phone' => '+22890222222',
            'password' => 'Secure123!',
            'password_confirmation' => 'Secure123!',
            'company_name' => 'Mensah Events',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.user.firstName', 'Pierre')
            ->assertJsonPath('data.user.lastName', 'Mensah')
            ->assertJsonPath('data.user.email', 'pierre.mensah@example.com')
            ->assertJsonPath('data.user.phone', '+22890222222');

        $this->assertDatabaseHas('organizers', [
            'company_name' => 'Mensah Events',
            'description' => null,
            'website' => null,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function it_fails_when_email_already_exists(): void
    {
        Role::create(['name' => 'organizer-manager', 'guard_name' => 'web']);

        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $payload = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'existing@example.com',
            'phone' => '+22890333333',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'company_name' => 'Test Company',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_fails_when_phone_already_exists(): void
    {
        Role::create(['name' => 'organizer-manager', 'guard_name' => 'web']);

        User::factory()->create([
            'phone' => '+22890444444',
        ]);

        $payload = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'phone' => '+22890444444',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'company_name' => 'Test Company',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    /** @test */
    public function it_fails_when_password_is_too_short(): void
    {
        $payload = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'phone' => '+22890555555',
            'password' => 'short',
            'password_confirmation' => 'short',
            'company_name' => 'Test Company',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function it_fails_when_password_confirmation_does_not_match(): void
    {
        $payload = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'phone' => '+22890666666',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword123!',
            'company_name' => 'Test Company',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function it_fails_when_company_name_is_missing(): void
    {
        $payload = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'phone' => '+22890777777',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['company_name']);
    }

    /** @test */
    public function it_fails_when_website_is_invalid(): void
    {
        $payload = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'phone' => '+22890888888',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'company_name' => 'Test Company',
            'website' => 'not-a-valid-url',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['website']);
    }

    /** @test */
    public function it_fails_when_required_fields_are_missing(): void
    {
        $response = $this->postJson($this->endpoint, []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'first_name',
                'last_name',
                'email',
                'phone',
                'password',
                'company_name',
            ]);
    }

    /** @test */
    public function it_creates_organizer_with_pending_status(): void
    {
        Notification::fake();

        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'organizer-manager', 'guard_name' => 'web']);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $payload = [
            'first_name' => 'Status',
            'last_name' => 'Check',
            'email' => 'status@example.com',
            'phone' => '+22890999999',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'company_name' => 'Status Check Company',
        ];

        $this->postJson($this->endpoint, $payload);

        $organizer = Organizer::where('company_name', 'Status Check Company')->first();
        $this->assertNotNull($organizer);
        $this->assertEquals('pending', $organizer->status->value);
    }
}
