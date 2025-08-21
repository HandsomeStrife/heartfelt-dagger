<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Character\Models\Character;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Room\Models\RoomParticipant>
 */
class RoomParticipantFactory extends Factory
{
    protected $model = RoomParticipant::class;

    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'user_id' => User::factory(),
            'character_id' => Character::factory(),
            'character_name' => null,
            'character_class' => null,
            'joined_at' => now(),
            'left_at' => null,
        ];
    }

    public function withoutCharacter(): static
    {
        return $this->state(fn (array $attributes) => [
            'character_id' => null,
        ]);
    }

    public function withTemporaryCharacter(): static
    {
        return $this->state(fn (array $attributes) => [
            'character_id' => null,
            'character_name' => $this->faker->firstName,
            'character_class' => $this->faker->randomElement(['Bard', 'Druid', 'Guardian', 'Ranger', 'Rogue', 'Seraph', 'Sorcerer', 'Warrior', 'Wizard']),
        ]);
    }

    public function leftAt($timestamp): static
    {
        return $this->state(fn (array $attributes) => [
            'left_at' => $timestamp,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'left_at' => null,
        ]);
    }
}
