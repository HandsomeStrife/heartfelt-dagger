<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Room\Enums\RecordingErrorType;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecording;
use Domain\Room\Models\RoomRecordingError;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomRecordingErrorFactory extends Factory
{
    protected $model = RoomRecordingError::class;

    public function definition(): array
    {
        $providers = ['wasabi', 'google_drive', 'local'];

        return [
            'room_id' => Room::factory(),
            'user_id' => User::factory(),
            'recording_id' => RoomRecording::factory(),
            'error_type' => fake()->randomElement(RecordingErrorType::cases())->value,
            'error_code' => fake()->optional()->randomElement(['400', '500', '502', '503', 'NETWORK_ERROR', 'TIMEOUT']),
            'error_message' => fake()->sentence(),
            'error_context' => fake()->optional()->randomElement([
                ['request_size' => '5MB', 'timeout' => '30s'],
                ['part_number' => 5, 'etag' => 'abc123'],
                ['stack_trace' => 'Error at line 42...'],
                null,
            ]),
            'provider' => fake()->randomElement($providers),
            'multipart_upload_id' => fake()->optional()->uuid(),
            'provider_file_id' => fake()->optional()->filePath(),
            'occurred_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'resolved' => fake()->boolean(20), // 20% chance of being resolved
            'resolution_notes' => fake()->optional()->sentence(),
            'resolved_at' => fake()->optional()->dateTimeBetween('-1 week', 'now'),
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'resolved' => true,
            'resolution_notes' => fake()->sentence(),
            'resolved_at' => fake()->dateTimeBetween($attributes['occurred_at'] ?? '-1 week', 'now'),
        ]);
    }

    public function unresolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'resolved' => false,
            'resolution_notes' => null,
            'resolved_at' => null,
        ]);
    }

    public function withErrorType(RecordingErrorType $errorType): static
    {
        return $this->state(fn (array $attributes) => [
            'error_type' => $errorType->value,
        ]);
    }

    public function withProvider(string $provider): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => $provider,
        ]);
    }
}
