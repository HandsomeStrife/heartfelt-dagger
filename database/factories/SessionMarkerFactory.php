<?php

namespace Database\Factories;

use Domain\Room\Models\Room;
use Domain\Room\Models\SessionMarker;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Room\Models\SessionMarker>
 */
class SessionMarkerFactory extends Factory
{
    protected $model = SessionMarker::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'identifier' => $this->faker->randomElement([
                'Session Start',
                'Session Stop',
                'Break Start',
                'Break Stop',
                null,
                $this->faker->words(2, true),
            ]),
            'creator_id' => User::factory(),
            'user_id' => User::factory(),
            'room_id' => Room::factory(),
            'recording_id' => null,
            'video_time' => $this->faker->optional(0.8)->numberBetween(0, 7200), // 0-2 hours
            'stt_time' => $this->faker->optional(0.8)->numberBetween(0, 7200), // 0-2 hours
        ];
    }

    /**
     * Create a marker with a specific identifier
     */
    public function withIdentifier(string $identifier): static
    {
        return $this->state(fn (array $attributes) => [
            'identifier' => $identifier,
        ]);
    }

    /**
     * Create a marker with specific timing
     */
    public function withTiming(?int $videoTime = null, ?int $sttTime = null): static
    {
        return $this->state(fn (array $attributes) => [
            'video_time' => $videoTime,
            'stt_time' => $sttTime,
        ]);
    }

    /**
     * Create a marker for a specific room and creator
     */
    public function forRoomAndCreator(int $roomId, int $creatorId, ?int $userId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'room_id' => $roomId,
            'creator_id' => $creatorId,
            'user_id' => $userId ?? $creatorId,
        ]);
    }

    /**
     * Create markers that share the same UUID
     */
    public function withSharedUuid(string $uuid): static
    {
        return $this->state(fn (array $attributes) => [
            'uuid' => $uuid,
        ]);
    }
}
