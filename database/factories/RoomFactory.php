<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Campaign\Models\Campaign;
use Domain\Room\Enums\RoomStatus;
use Domain\Room\Models\Room;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Room\Models\Room>
 */
class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(rand(2, 4), true),
            'description' => $this->faker->paragraph,
            'password' => bcrypt('password'),
            'guest_count' => $this->faker->numberBetween(2, 6), // Total capacity including creator
            'creator_id' => User::factory(),
            'status' => RoomStatus::Active,
        ];
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RoomStatus::Archived,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RoomStatus::Completed,
        ]);
    }

    public function withGuestCount(int $count): static
    {
        return $this->state(fn (array $attributes) => [
            'guest_count' => $count,
        ]);
    }

    public function forCampaign(Campaign $campaign): static
    {
        return $this->state(fn (array $attributes) => [
            'campaign_id' => $campaign->id,
            'creator_id' => $campaign->creator_id,
        ]);
    }

    public function passwordless(): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => null,
        ]);
    }
}
