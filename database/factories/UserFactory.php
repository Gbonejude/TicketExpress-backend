<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Gender;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    public function configure(): UserFactory|Factory
    {
        return $this->afterCreating(callback: function (User $user): void {
            // Create a fake image file
            $image = UploadedFile::fake()->image('user.jpg', 600, 600);

            // Attach the image to the 'products' collection
            $user->addMedia($image)
                ->toMediaCollection('users');
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'last_name' => fake()->lastName(),
            'first_name' => fake()->firstName(),
            'address' => fake()->address(),
            'birthday' => fake()->date(),
            'password' => self::$password ??= bcrypt(value: 'password'),
            'gender' => fake()->randomElement(
                array: array_column(
                    array: Gender::cases(),
                    column_key: 'value',
                ),
            ),
            'remember_token' => Str::random(length: 10),
        ];
    }

    /**
     * Indicate that the user is inactive.
     *
     * @return Factory
     */
    public function inactive(): self
    {
        return $this->state([
            'active' => false, // Utilisateur inactif
        ]);
    }

    /**
     * Indicate that the model's email has been verified.
     *
     * @return Factory
     */
    public function verified(): self
    {
        return $this->state([
            'email_verified_at' => now(), // Date de vérification de l'email
        ]);
    }
}
