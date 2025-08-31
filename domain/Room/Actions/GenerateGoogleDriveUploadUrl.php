<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Models\Room;
use Domain\Room\Services\GoogleDriveService;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Illuminate\Support\Facades\Log;

class GenerateGoogleDriveUploadUrl
{
    public function execute(
        Room $room,
        User $user,
        string $filename,
        string $contentType,
        int $sizeBytes,
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
        $this->validateFileConstraints($filename, $contentType, $sizeBytes);

        try {
            // Initialize Google Drive service
            $driveService = new GoogleDriveService($storageAccount);

            // Generate filename with timestamp and user info
            $originalName = pathinfo($filename, PATHINFO_FILENAME);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $timestamp = now()->format('Y-m-d_H-i-s');
            $uniqueFilename = "room_{$room->id}_user_{$user->id}_{$timestamp}_{$originalName}.{$extension}";

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

            // Generate direct upload URL
            $result = $driveService->generateDirectUploadUrl(
                $uniqueFilename,
                $contentType,
                $sizeBytes,
                $uploadMetadata
            );

            Log::info('Generated Google Drive direct upload URL', [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'filename' => $uniqueFilename,
                'size_bytes' => $sizeBytes,
                'storage_account_id' => $storageAccount->id,
            ]);

            return [
                'success' => true,
                'upload_url' => $result['upload_url'],
                'session_uri' => $result['session_uri'],
                'filename' => $uniqueFilename,
                'expires_at' => $result['expires_at'],
                'metadata' => [
                    'provider' => 'google_drive',
                    'provider_file_id' => null, // Will be filled after upload completion
                    'storage_account_id' => $storageAccount->id,
                    'room_id' => $room->id,
                    'user_id' => $user->id,
                    'filename' => $uniqueFilename,
                    'size_bytes' => $sizeBytes,
                    'content_type' => $contentType,
                    'session_uri' => $result['session_uri'],
                    ...$metadata,
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate Google Drive upload URL', [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to generate upload URL: ' . $e->getMessage());
        }
    }

    /**
     * Validate file constraints for Google Drive uploads
     */
    private function validateFileConstraints(string $filename, string $contentType, int $sizeBytes): void
    {
        // Check file size (Google Drive allows up to 5TB, but we'll set a reasonable limit)
        $maxSizeBytes = 1024 * 1024 * 1024 * 2; // 2GB limit for video recordings
        if ($sizeBytes > $maxSizeBytes) {
            throw new \Exception('File size exceeds maximum allowed size of 2GB');
        }

        // Check content type
        $allowedTypes = ['video/webm', 'video/mp4', 'video/quicktime'];
        if (!in_array($contentType, $allowedTypes)) {
            throw new \Exception('Invalid content type. Allowed types: ' . implode(', ', $allowedTypes));
        }

        // Check filename
        if (empty($filename) || strlen($filename) > 255) {
            throw new \Exception('Invalid filename. Must be between 1 and 255 characters');
        }
    }

    /**
     * Get or create a folder for the room's recordings in Google Drive
     */
    private function getRoomFolder(GoogleDriveService $driveService, Room $room): ?string
    {
        try {
            // Try to find existing folder structure
            $parentFolderName = 'DaggerHeart Recordings';
            $roomFolderName = "Room: {$room->name}";

            // For now, we'll just return null and let files go to root
            // In a full implementation, you'd search for/create the folder hierarchy
            // This requires additional Google Drive API calls that we'll skip for this demo

            return null;

        } catch (\Exception $e) {
            Log::warning('Failed to get/create room folder in Google Drive', [
                'room_id' => $room->id,
                'error' => $e->getMessage(),
            ]);
            
            // Return null to upload to root directory
            return null;
        }
    }
}
