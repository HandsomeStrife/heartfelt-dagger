<?php

declare(strict_types=1);

namespace Domain\Room\Models;

use Domain\User\Models\UserStorageAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomRecordingSettings extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'recording_enabled' => 'boolean',
        'stt_enabled' => 'boolean',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function storageAccount(): BelongsTo
    {
        return $this->belongsTo(UserStorageAccount::class, 'storage_account_id');
    }

    /**
     * Check if recording is enabled for this room
     */
    public function isRecordingEnabled(): bool
    {
        return $this->recording_enabled;
    }

    /**
     * Check if speech-to-text is enabled for this room
     */
    public function isSttEnabled(): bool
    {
        return $this->stt_enabled;
    }

    /**
     * Check if the room has a storage provider configured
     */
    public function hasStorageProvider(): bool
    {
        return !empty($this->storage_provider) && !empty($this->storage_account_id);
    }

    /**
     * Check if using Wasabi storage
     */
    public function isUsingWasabi(): bool
    {
        return $this->storage_provider === 'wasabi';
    }

    /**
     * Check if using Google Drive storage
     */
    public function isUsingGoogleDrive(): bool
    {
        return $this->storage_provider === 'google_drive';
    }

    /**
     * Enable recording with the specified storage provider
     */
    public function enableRecording(string $provider, UserStorageAccount $storageAccount): void
    {
        $this->update([
            'recording_enabled' => true,
            'storage_provider' => $provider,
            'storage_account_id' => $storageAccount->id,
        ]);
    }

    /**
     * Disable recording
     */
    public function disableRecording(): void
    {
        $this->update([
            'recording_enabled' => false,
        ]);
    }

    /**
     * Enable speech-to-text
     */
    public function enableStt(): void
    {
        $this->update(['stt_enabled' => true]);
    }

    /**
     * Disable speech-to-text
     */
    public function disableStt(): void
    {
        $this->update(['stt_enabled' => false]);
    }

    /**
     * Check if STT consent is required (vs optional)
     */
    public function isSttConsentRequired(): bool
    {
        return $this->stt_consent_requirement === 'required';
    }

    /**
     * Check if recording consent is required (vs optional)
     */
    public function isRecordingConsentRequired(): bool
    {
        return $this->recording_consent_requirement === 'required';
    }

    protected static function newFactory()
    {
        return \Database\Factories\RoomRecordingSettingsFactory::new();
    }
}
