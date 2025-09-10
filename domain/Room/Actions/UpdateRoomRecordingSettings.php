<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Illuminate\Support\Facades\Log;

class UpdateRoomRecordingSettings
{
    public function execute(
        Room $room,
        User $user,
        bool $recordingEnabled,
        bool $sttEnabled,
        ?string $storageProvider = null,
        ?int $storageAccountId = null,
        ?string $sttProvider = null,
        ?int $sttAccountId = null,
        string $sttConsentRequirement = 'optional',
        string $recordingConsentRequirement = 'optional',
        ?string $viewerPassword = null
    ): RoomRecordingSettings {
        // Verify user is the room creator
        if ($room->creator_id !== $user->id) {
            throw new \Exception('Only the room creator can modify recording settings');
        }

        // Validate storage account if provided
        if ($storageAccountId) {
            $storageAccount = UserStorageAccount::where('id', $storageAccountId)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->first();

            if (! $storageAccount) {
                throw new \Exception('Storage account not found or not accessible');
            }

            // Ensure storage provider matches account provider
            if ($storageProvider && $storageAccount->provider !== $storageProvider) {
                throw new \Exception('Storage provider mismatch with selected account');
            }

            $storageProvider = $storageAccount->provider;
        }

        // Validate STT account if provided
        if ($sttAccountId) {
            $sttAccount = UserStorageAccount::where('id', $sttAccountId)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->first();

            if (! $sttAccount) {
                throw new \Exception('STT account not found or not accessible');
            }

            // Ensure STT provider matches account provider
            if ($sttProvider && $sttAccount->provider !== $sttProvider) {
                throw new \Exception('STT provider mismatch with selected account');
            }

            $sttProvider = $sttAccount->provider;
        }

        // If recording is disabled, clear storage settings
        if (! $recordingEnabled) {
            $storageProvider = null;
            $storageAccountId = null;
        }

        // If STT is disabled, clear STT settings
        if (! $sttEnabled) {
            $sttProvider = null;
            $sttAccountId = null;
        }

        // If recording is enabled but no storage provider specified, default to local_device
        if ($recordingEnabled && ! $storageProvider) {
            $storageProvider = 'local_device';
            $storageAccountId = null;
        }

        // If STT is enabled but no provider specified, default to browser
        if ($sttEnabled && ! $sttProvider) {
            $sttProvider = 'browser';
            $sttAccountId = null;
        }

        try {
            // Get or create recording settings
            $settings = RoomRecordingSettings::firstOrNew(['room_id' => $room->id]);

            $settings->recording_enabled = $recordingEnabled;
            $settings->stt_enabled = $sttEnabled;
            $settings->storage_provider = $storageProvider;
            $settings->storage_account_id = $storageAccountId;
            $settings->stt_provider = $sttProvider;
            $settings->stt_account_id = $sttAccountId;
            $settings->stt_consent_requirement = $sttConsentRequirement;
            $settings->recording_consent_requirement = $recordingConsentRequirement;

            // Handle viewer password - hash if provided, null if empty
            if ($viewerPassword) {
                $settings->viewer_password = \Hash::make($viewerPassword);
            } else {
                $settings->viewer_password = null;
            }

            $settings->save();

            // If STT is disabled, reset all participant STT consent for this room
            if (! $sttEnabled) {
                $room->participants()->update([
                    'stt_consent_given' => null,
                    'stt_consent_at' => null,
                ]);
            }

            Log::info('Updated room recording settings', [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'recording_enabled' => $recordingEnabled,
                'stt_enabled' => $sttEnabled,
                'storage_provider' => $storageProvider,
                'storage_account_id' => $storageAccountId,
                'stt_provider' => $sttProvider,
                'stt_account_id' => $sttAccountId,
            ]);

            return $settings;

        } catch (\Exception $e) {
            Log::error('Failed to update room recording settings', [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to update recording settings: '.$e->getMessage());
        }
    }
}
