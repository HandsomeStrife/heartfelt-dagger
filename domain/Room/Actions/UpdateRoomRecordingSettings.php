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
        ?int $storageAccountId = null
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

            if (!$storageAccount) {
                throw new \Exception('Storage account not found or not accessible');
            }

            // Ensure storage provider matches account provider
            if ($storageProvider && $storageAccount->provider !== $storageProvider) {
                throw new \Exception('Storage provider mismatch with selected account');
            }

            $storageProvider = $storageAccount->provider;
        }

        // If recording is disabled, clear storage settings
        if (!$recordingEnabled) {
            $storageProvider = null;
            $storageAccountId = null;
        }

        // If recording is enabled but no storage provider specified, default to local
        if ($recordingEnabled && !$storageProvider) {
            $storageProvider = 'local';
            $storageAccountId = null;
        }

        try {
            // Get or create recording settings
            $settings = RoomRecordingSettings::firstOrNew(['room_id' => $room->id]);
            
            $settings->recording_enabled = $recordingEnabled;
            $settings->stt_enabled = $sttEnabled;
            $settings->storage_provider = $storageProvider;
            $settings->storage_account_id = $storageAccountId;
            
            $settings->save();

            // If STT is disabled but recording consent was previously enabled,
            // we might want to reset participant consent (optional business logic)
            if (!$sttEnabled) {
                // Reset all participant STT consent for this room
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
            ]);

            return $settings;

        } catch (\Exception $e) {
            Log::error('Failed to update room recording settings', [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to update recording settings: ' . $e->getMessage());
        }
    }
}

