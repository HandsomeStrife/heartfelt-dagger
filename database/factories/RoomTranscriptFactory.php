<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomTranscript;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomTranscriptFactory extends Factory
{
    protected $model = RoomTranscript::class;

    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'user_id' => User::factory(),
            'started_at_ms' => $this->faker->numberBetween(1000000, 9999999),
            'ended_at_ms' => fn (array $attributes) => $attributes['started_at_ms'] + $this->faker->numberBetween(1000, 5000),
            'text' => $this->faker->sentence(),
            'language' => 'en-US',
            'provider' => 'browser',
            'confidence' => $this->faker->randomFloat(2, 0.5, 1.0),
            'character_id' => null,
            'character_name' => $this->faker->optional()->firstName(),
            'character_class' => $this->faker->optional()->randomElement([
                'Bard', 'Druid', 'Guardian', 'Ranger', 'Rogue', 'Seraph', 'Sorcerer', 'Warrior', 'Wizard'
            ]),
        ];
    }

    public function withCharacter(string $name, string $class): static
    {
        return $this->state([
            'character_name' => $name,
            'character_class' => $class,
        ]);
    }

    public function withText(string $text): static
    {
        return $this->state([
            'text' => $text,
        ]);
    }

    public function withTimestamp(int $startMs, int $endMs = null): static
    {
        return $this->state([
            'started_at_ms' => $startMs,
            'ended_at_ms' => $endMs ?: $startMs + 2000,
        ]);
    }

    public function lowConfidence(): static
    {
        return $this->state([
            'confidence' => $this->faker->randomFloat(2, 0.1, 0.7),
        ]);
    }

    public function highConfidence(): static
    {
        return $this->state([
            'confidence' => $this->faker->randomFloat(2, 0.8, 1.0),
        ]);
    }
}
