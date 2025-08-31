<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\UserStorageAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Room\Models\RoomRecordingSettings>
 */
class RoomRecordingSettingsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RoomRecordingSettings::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'recording_enabled' => false,
            'stt_enabled' => false,
            'storage_provider' => null,
            'storage_account_id' => null,
        ];
    }

    /**
     * Indicate that recording is enabled.
     */
    public function recordingEnabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'recording_enabled' => true,
        ]);
    }

    /**
     * Indicate that STT is enabled.
     */
    public function sttEnabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'stt_enabled' => true,
        ]);
    }

    /**
     * Configure for local storage.
     */
    public function withLocalStorage(): static
    {
        return $this->state(fn (array $attributes) => [
            'recording_enabled' => true,
            'storage_provider' => 'local',
            'storage_account_id' => null,
        ]);
    }

    /**
     * Configure for Wasabi storage.
     */
    public function withWasabiStorage(?UserStorageAccount $storageAccount = null): static
    {
        return $this->state(fn (array $attributes) => [
            'recording_enabled' => true,
            'storage_provider' => 'wasabi',
            'storage_account_id' => $storageAccount?->id ?? UserStorageAccount::factory()->wasabi()->create()->id,
        ]);
    }

    /**
     * Configure for Google Drive storage.
     */
    public function withGoogleDriveStorage(?UserStorageAccount $storageAccount = null): static
    {
        return $this->state(fn (array $attributes) => [
            'recording_enabled' => true,
            'storage_provider' => 'google_drive',
            'storage_account_id' => $storageAccount?->id ?? UserStorageAccount::factory()->googleDrive()->create()->id,
        ]);
    }
}
