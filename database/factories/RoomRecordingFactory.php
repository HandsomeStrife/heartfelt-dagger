<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecording;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomRecordingFactory extends Factory
{
    protected $model = RoomRecording::class;

    public function definition(): array
    {
        $startedAt = \Carbon\Carbon::instance(fake()->dateTimeBetween('-1 month', 'now'));
        $endedAt = \Carbon\Carbon::instance(fake()->dateTimeBetween($startedAt, $startedAt->format('Y-m-d H:i:s').' +2 hours'));

        return [
            'room_id' => Room::factory(),
            'user_id' => User::factory(),
            'provider' => fake()->randomElement(['wasabi', 'google_drive', 'local']),
            'provider_file_id' => fake()->uuid(),
            'filename' => fake()->words(3, true).'_recording.webm',
            'size_bytes' => fake()->numberBetween(1000000, 500000000), // 1MB to 500MB
            'started_at_ms' => $startedAt->getTimestamp() * 1000,
            'ended_at_ms' => $endedAt->getTimestamp() * 1000,
            'mime_type' => fake()->randomElement(['video/webm', 'video/mp4']),
            'status' => fake()->randomElement(['processing', 'ready', 'uploaded', 'failed']),
        ];
    }

    public function ready(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ready',
        ]);
    }

    public function uploaded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'uploaded',
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }

    public function wasabi(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'wasabi',
        ]);
    }

    public function googleDrive(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'google_drive',
        ]);
    }

    public function local(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'local',
        ]);
    }

    public function large(): static
    {
        return $this->state(fn (array $attributes) => [
            'size_bytes' => fake()->numberBetween(100000000, 2000000000), // 100MB to 2GB
        ]);
    }

    public function small(): static
    {
        return $this->state(fn (array $attributes) => [
            'size_bytes' => fake()->numberBetween(1000000, 50000000), // 1MB to 50MB
        ]);
    }

    public function recent(): static
    {
        $startedAt = \Carbon\Carbon::instance(fake()->dateTimeBetween('-1 week', 'now'));
        $endedAt = \Carbon\Carbon::instance(fake()->dateTimeBetween($startedAt, $startedAt->format('Y-m-d H:i:s').' +2 hours'));

        return $this->state(fn (array $attributes) => [
            'started_at_ms' => $startedAt->getTimestamp() * 1000,
            'ended_at_ms' => $endedAt->getTimestamp() * 1000,
        ]);
    }

    public function old(): static
    {
        $startedAt = \Carbon\Carbon::instance(fake()->dateTimeBetween('-3 months', '-1 month'));
        $endedAt = \Carbon\Carbon::instance(fake()->dateTimeBetween($startedAt, $startedAt->format('Y-m-d H:i:s').' +2 hours'));

        return $this->state(fn (array $attributes) => [
            'started_at_ms' => $startedAt->getTimestamp() * 1000,
            'ended_at_ms' => $endedAt->getTimestamp() * 1000,
        ]);
    }

    public function withThumbnail(): static
    {
        return $this->state(fn (array $attributes) => [
            'thumbnail_url' => fake()->imageUrl(320, 180, 'video'),
        ]);
    }

    public function withStreamUrl(): static
    {
        return $this->state(fn (array $attributes) => [
            'stream_url' => fake()->url().'/video.webm',
        ]);
    }
}
