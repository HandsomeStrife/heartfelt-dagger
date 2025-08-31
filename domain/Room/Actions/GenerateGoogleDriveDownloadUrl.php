<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecording;
use Domain\Room\Services\GoogleDriveService;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Illuminate\Support\Facades\Log;

class GenerateGoogleDriveDownloadUrl
{
    public function execute(
        Room $room,
        RoomRecording $recording,
        User $user
    ): array {
        // Validate that user has access to this recording
        if (!$room->isCreator($user) && !$room->hasActiveParticipant($user)) {
            throw new \Exception('Only room participants can download recordings');
        }

        // Validate that recording belongs to this room
        if ($recording->room_id !== $room->id) {
            throw new \Exception('Recording does not belong to this room');
        }

        // Validate storage provider
        if ($recording->provider !== 'google_drive') {
            throw new \Exception('Recording is not stored on Google Drive');
        }

        // Get the storage account
        $room->load('recordingSettings.storageAccount');
        $storageAccount = $room->recordingSettings?->storageAccount;
        
        if (!$storageAccount || $storageAccount->provider !== 'google_drive') {
            throw new \Exception('Google Drive storage account not found or invalid');
        }

        try {
            // Initialize Google Drive service
            $driveService = new GoogleDriveService($storageAccount);

            // Get download URL for the file
            $downloadData = $driveService->getDownloadUrl($recording->provider_file_id);

            Log::info('Generated Google Drive download URL', [
                'room_id' => $room->id,
                'recording_id' => $recording->id,
                'user_id' => $user->id,
                'file_id' => $recording->provider_file_id,
                'storage_account_id' => $storageAccount->id,
            ]);

            return [
                'success' => true,
                'download_url' => $downloadData['download_url'],
                'web_view_link' => $downloadData['web_view_link'],
                'filename' => $recording->filename,
                'size_bytes' => $recording->size_bytes,
                'content_type' => $recording->mime_type,
                'provider' => 'google_drive',
                'created_time' => $downloadData['created_time'],
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate Google Drive download URL', [
                'room_id' => $room->id,
                'recording_id' => $recording->id,
                'user_id' => $user->id,
                'file_id' => $recording->provider_file_id,
                'error' => $e->getMessage(),
                'storage_account_id' => $storageAccount->id ?? null,
            ]);

            throw new \Exception('Failed to generate download URL: ' . $e->getMessage());
        }
    }
}

