<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserStorageAccount>
 */
class UserStorageAccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserStorageAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider' => fake()->randomElement(['wasabi', 'google_drive']),
            'display_name' => fake()->company() . ' Storage',
            'is_active' => fake()->boolean(80), // 80% chance of being active
            'encrypted_credentials' => [],
        ];
    }

    /**
     * Indicate that the account is for Wasabi storage.
     */
    public function wasabi(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'provider' => 'wasabi',
                'display_name' => fake()->company() . ' Wasabi',
                'encrypted_credentials' => [
                    'access_key_id' => fake()->regexify('[A-Z0-9]{20}'),
                    'secret_access_key' => fake()->regexify('[a-zA-Z0-9+/]{40}'),
                    'bucket' => fake()->slug() . '-bucket',
                    'region' => fake()->randomElement(['us-east-1', 'us-west-1', 'us-west-2', 'eu-west-1']),
                    'endpoint' => 'https://s3.us-east-1.wasabisys.com',
                ],
            ];
        });
    }

    /**
     * Indicate that the account is for Google Drive storage.
     */
    public function googleDrive(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'provider' => 'google_drive',
                'display_name' => fake()->firstName() . '\'s Google Drive',
                'encrypted_credentials' => [
                    'refresh_token' => fake()->regexify('[a-zA-Z0-9_-]{40}'),
                    'email' => fake()->email(),
                    'folder_id' => fake()->regexify('[a-zA-Z0-9_-]{33}'),
                ],
            ];
        });
    }

    /**
     * Indicate that the account is active.
     */
    public function active(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }

    /**
     * Indicate that the account is inactive.
     */
    public function inactive(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
}
