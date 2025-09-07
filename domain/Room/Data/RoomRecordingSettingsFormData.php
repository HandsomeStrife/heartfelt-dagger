<?php

declare(strict_types=1);

namespace Domain\Room\Data;

use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;
use Livewire\Wireable;

class RoomRecordingSettingsFormData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public bool $recording_enabled,
        
        public bool $stt_enabled,
        
        #[Nullable]
        #[In(['local_device', 'wasabi', 'google_drive'])]
        public ?string $storage_provider,
        
        #[Nullable]
        public ?int $storage_account_id,
        
        #[Nullable]
        #[In(['browser', 'assemblyai'])]
        public ?string $stt_provider,
        
        #[Nullable]
        public ?int $stt_account_id,
        
        #[In(['optional', 'required'])]
        public string $stt_consent_requirement = 'optional',
        
        #[In(['optional', 'required'])]
        public string $recording_consent_requirement = 'optional',
        
        #[Nullable]
        public ?string $viewer_password,
    ) {}

    public static function fromRoomRecordingSettings(?RoomRecordingSettingsData $settings): self
    {
        if (!$settings) {
            return new self(
                recording_enabled: false,
                stt_enabled: false,
                storage_provider: null,
                storage_account_id: null,
                stt_provider: null,
                stt_account_id: null,
                stt_consent_requirement: 'optional',
                recording_consent_requirement: 'optional',
                viewer_password: null,
            );
        }

        return new self(
            recording_enabled: $settings->recording_enabled,
            stt_enabled: $settings->stt_enabled,
            storage_provider: $settings->storage_provider,
            storage_account_id: $settings->storage_account_id,
            stt_provider: $settings->stt_provider,
            stt_account_id: $settings->stt_account_id,
            stt_consent_requirement: $settings->stt_consent_requirement,
            recording_consent_requirement: $settings->recording_consent_requirement,
            viewer_password: $settings->viewer_password,
        );
    }

    public function getStorageProviderDisplayName(): string
    {
        return match ($this->storage_provider) {
            'local_device' => 'Local Device Recording',
            'wasabi' => 'Wasabi Cloud Storage',
            'google_drive' => 'Google Drive',
            default => 'No Storage Selected',
        };
    }

    public function requiresStorageAccount(): bool
    {
        return in_array($this->storage_provider, ['wasabi', 'google_drive']);
    }

    public function getSttProviderDisplayName(): string
    {
        return match ($this->stt_provider) {
            'browser' => 'Browser Speech Recognition',
            'assemblyai' => 'AssemblyAI',
            default => 'Browser Speech Recognition (Default)',
        };
    }

    public function requiresSttAccount(): bool
    {
        return $this->stt_provider === 'assemblyai';
    }

    public function isValid(): bool
    {
        // If recording is enabled, must have a storage provider
        if ($this->recording_enabled && !$this->storage_provider) {
            return false;
        }

        // If storage provider requires an account, must have one selected
        if ($this->recording_enabled && $this->requiresStorageAccount() && !$this->storage_account_id) {
            return false;
        }

        // If STT is enabled and requires an account, must have one selected
        if ($this->stt_enabled && $this->requiresSttAccount() && !$this->stt_account_id) {
            return false;
        }

        return true;
    }

    public function getValidationMessage(): ?string
    {
        if ($this->recording_enabled && !$this->storage_provider) {
            return 'Please select a storage provider when recording is enabled.';
        }

        if ($this->recording_enabled && $this->requiresStorageAccount() && !$this->storage_account_id) {
            return match ($this->storage_provider) {
                'wasabi' => 'Please select a Wasabi storage account or create one.',
                'google_drive' => 'Please select a Google Drive account or connect one.',
                default => 'Please select a storage account.',
            };
        }

        if ($this->stt_enabled && $this->requiresSttAccount() && !$this->stt_account_id) {
            return 'Please select an AssemblyAI account or create one.';
        }

        return null;
    }
}

