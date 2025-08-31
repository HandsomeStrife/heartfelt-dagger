<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Models\Room;
use Domain\Room\Services\GoogleDriveService;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class UploadToGoogleDrive
{
    public function execute(
        Room $room,
        User $user,
        UploadedFile $file,
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

        // Validate file constraints
        $this->validateFileConstraints($file);

        try {
            // Initialize Google Drive service
            $driveService = new GoogleDriveService($storageAccount);

            // Generate filename with timestamp and user info
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "room_{$room->id}_user_{$user->id}_{$timestamp}_{$originalName}.{$extension}";

            // Add metadata for the upload
            $uploadMetadata = [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'started_at_ms' => $metadata['started_at_ms'] ?? null,
                'ended_at_ms' => $metadata['ended_at_ms'] ?? null,
                ...$metadata,
            ];

            // Get or create room folder
            $folderId = $this->getRoomFolder($driveService, $room);
            if ($folderId) {
                $uploadMetadata['folder_id'] = $folderId;
            }

            // Upload the file
            $result = $driveService->uploadFile($file, $filename, $uploadMetadata);

            Log::info('Successfully uploaded file to Google Drive', [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'file_id' => $result['file_id'],
                'filename' => $filename,
                'size' => $file->getSize(),
                'storage_account_id' => $storageAccount->id,
            ]);

            return [
                'success' => true,
                'provider' => 'google_drive',
                'provider_file_id' => $result['file_id'],
                'filename' => $filename,
                'size_bytes' => $file->getSize(),
                'web_view_link' => $result['web_view_link'],
                'web_content_link' => $result['web_content_link'],
                'created_time' => $result['created_time'],
                'storage_account_id' => $storageAccount->id,
                'room_id' => $room->id,
                'user_id' => $user->id,
                'metadata' => $uploadMetadata,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to upload file to Google Drive', [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'filename' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
                'storage_account_id' => $storageAccount->id ?? null,
            ]);

            throw new \Exception('Failed to upload to Google Drive: ' . $e->getMessage());
        }
    }

    private function validateFileConstraints(UploadedFile $file): void
    {
        // Check file size (100MB max)
        $maxSize = 100 * 1024 * 1024; // 100MB
        if ($file->getSize() > $maxSize) {
            throw new \Exception('File size exceeds maximum allowed size of 100MB');
        }

        // Check content type
        $allowedTypes = ['video/webm', 'video/mp4', 'video/quicktime'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            throw new \Exception('File type not allowed. Only WebM, MP4, and QuickTime videos are supported.');
        }

        // Check filename
        $filename = $file->getClientOriginalName();
        if (empty($filename) || strlen($filename) > 255) {
            throw new \Exception('Invalid filename. Must be between 1 and 255 characters.');
        }
    }

    private function getRoomFolder(GoogleDriveService $driveService, Room $room): ?string
    {
        // Check if we already have a folder ID stored for this room
        $room->load('recordingSettings');
        $settings = $room->recordingSettings;
        
        // For now, we'll store the folder ID in the room's metadata or create it each time
        // In a production system, you might want to add a folder_id field to room_recording_settings
        
        // Try to create or get the folder
        try {
            return $driveService->createRoomFolder($room);
        } catch (\Exception $e) {
            Log::warning('Failed to create/get room folder, uploading to root', [
                'room_id' => $room->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}

