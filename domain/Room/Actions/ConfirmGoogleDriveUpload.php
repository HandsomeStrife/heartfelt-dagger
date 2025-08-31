<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecording;
use Domain\Room\Services\GoogleDriveService;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Illuminate\Support\Facades\Log;

class ConfirmGoogleDriveUpload
{
    public function execute(
        Room $room,
        User $user,
        string $sessionUri,
        array $metadata = []
    ): array {
        // Validate that recording is enabled for this room
        $room->load('recordingSettings');
        if (!$room->recordingSettings || !$room->recordingSettings->isRecordingEnabled()) {
            throw new \Exception('Video recording is not enabled for this room');
        }

        // Validate storage provider
        if ($room->recordingSettings->storage_provider !== 'google_drive') {
            throw new \Exception('Room is not configured for Google Drive storage');
        }

        // Get the storage account
        $storageAccount = UserStorageAccount::find($room->recordingSettings->storage_account_id);
        if (!$storageAccount || $storageAccount->provider !== 'google_drive') {
            throw new \Exception('Google Drive storage account not found or invalid');
        }

        // Ensure the storage account belongs to the room creator
        if ($storageAccount->user_id !== $room->creator_id) {
            throw new \Exception('Storage account does not belong to room creator');
        }

        try {
            // Initialize Google Drive service
            $driveService = new GoogleDriveService($storageAccount);

            // Verify upload completion and get file information
            $fileInfo = $driveService->verifyUploadCompletion($sessionUri);

            if (!$fileInfo['success']) {
                throw new \Exception('Upload verification failed');
            }

            // Create recording record in database
            $recording = RoomRecording::create([
                'room_id' => $room->id,
                'user_id' => $user->id,
                'provider' => 'google_drive',
                'provider_file_id' => $fileInfo['file_id'],
                'filename' => $metadata['filename'] ?? $fileInfo['filename'],
                'size_bytes' => $fileInfo['size'] ?? $metadata['size_bytes'] ?? 0,
                'started_at_ms' => $metadata['started_at_ms'] ?? 0,
                'ended_at_ms' => $metadata['ended_at_ms'] ?? 0,
                'mime_type' => $fileInfo['mime_type'] ?? $metadata['content_type'] ?? 'video/webm',
                'status' => 'uploaded',
            ]);

            Log::info('Confirmed Google Drive upload and created recording record', [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'recording_id' => $recording->id,
                'file_id' => $fileInfo['file_id'],
                'filename' => $recording->filename,
                'size_bytes' => $recording->size_bytes,
                'storage_account_id' => $storageAccount->id,
            ]);

            return [
                'success' => true,
                'recording_id' => $recording->id,
                'provider_file_id' => $fileInfo['file_id'],
                'filename' => $recording->filename,
                'size_bytes' => $recording->size_bytes,
                'web_view_link' => $fileInfo['web_view_link'],
                'web_content_link' => $fileInfo['web_content_link'],
                'created_time' => $fileInfo['created_time'],
            ];

        } catch (\Exception $e) {
            Log::error('Failed to confirm Google Drive upload', [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'session_uri' => $sessionUri,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to confirm upload: ' . $e->getMessage());
        }
    }
}
