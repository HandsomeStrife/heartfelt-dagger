<?php

declare(strict_types=1);

namespace Domain\Room\Data;

use Spatie\LaravelData\Attributes\Validation\Boolean;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;
use Livewire\Wireable;

class RoomRecordingSettingsFormData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        #[Boolean]
        public bool $recording_enabled,
        
        #[Boolean] 
        public bool $stt_enabled,
        
        #[Nullable]
        #[In(['local', 'wasabi', 'google_drive'])]
        public ?string $storage_provider,
        
        #[Nullable]
        public ?int $storage_account_id,
    ) {}

    public static function fromRoomRecordingSettings(?RoomRecordingSettingsData $settings): self
    {
        if (!$settings) {
            return new self(
                recording_enabled: false,
                stt_enabled: false,
                storage_provider: null,
                storage_account_id: null,
            );
        }

        return new self(
            recording_enabled: $settings->recording_enabled,
            stt_enabled: $settings->stt_enabled,
            storage_provider: $settings->storage_provider,
            storage_account_id: $settings->storage_account_id,
        );
    }

    public function getStorageProviderDisplayName(): string
    {
        return match ($this->storage_provider) {
            'local' => 'Local Server Storage',
            'wasabi' => 'Wasabi Cloud Storage',
            'google_drive' => 'Google Drive',
            default => 'No Storage Selected',
        };
    }

    public function requiresStorageAccount(): bool
    {
        return in_array($this->storage_provider, ['wasabi', 'google_drive']);
    }

    public function isValid(): bool
    {
        // If recording is disabled, settings are valid
        if (!$this->recording_enabled) {
            return true;
        }

        // If recording is enabled, must have a storage provider
        if (!$this->storage_provider) {
            return false;
        }

        // If storage provider requires an account, must have one selected
        if ($this->requiresStorageAccount() && !$this->storage_account_id) {
            return false;
        }

        return true;
    }

    public function getValidationMessage(): ?string
    {
        if (!$this->recording_enabled) {
            return null;
        }

        if (!$this->storage_provider) {
            return 'Please select a storage provider when recording is enabled.';
        }

        if ($this->requiresStorageAccount() && !$this->storage_account_id) {
            return match ($this->storage_provider) {
                'wasabi' => 'Please select a Wasabi storage account or create one.',
                'google_drive' => 'Please select a Google Drive account or connect one.',
                default => 'Please select a storage account.',
            };
        }

        return null;
    }
}

