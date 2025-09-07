<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Models\Room;
use Domain\Room\Services\WasabiS3Service;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use Illuminate\Support\Facades\Log;

class GenerateWasabiPresignedUrl
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
        if ($room->recordingSettings->storage_provider !== 'wasabi') {
            throw new \Exception('Room is not configured for Wasabi storage');
        }

        // Get the storage account
        $storageAccount = UserStorageAccount::find($room->recordingSettings->storage_account_id);
        if (!$storageAccount || $storageAccount->provider !== 'wasabi') {
            throw new \Exception('Wasabi storage account not found or invalid');
        }

        // Ensure the storage account belongs to the room creator
        if ($storageAccount->user_id !== $room->creator_id) {
            throw new \Exception('Storage account does not belong to room creator');
        }

        // Validate file constraints
        $this->validateFileConstraints($filename, $contentType, $sizeBytes);

        try {
            // Initialize Wasabi service
            $wasabiService = new WasabiS3Service($storageAccount);

            // Generate unique key for the recording
            $key = WasabiS3Service::generateRecordingKey($room, $user->id, $filename);

            // Generate presigned URL
            $presignedData = $wasabiService->generatePresignedUploadUrl(
                $key,
                $contentType,
                60 // 1 hour expiration
            );

            Log::info('Generated Wasabi presigned URL', [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'key' => $key,
                'filename' => $filename,
                'storage_account_id' => $storageAccount->id,
            ]);

            return [
                'success' => true,
                'presigned_url' => $presignedData['presigned_url'],
                'key' => $key,
                'bucket' => $presignedData['bucket'],
                'expires_at' => $presignedData['expires_at'],
                'headers' => $presignedData['headers'],
                'metadata' => [
                    'provider' => 'wasabi',
                    'provider_file_id' => $key,
                    'storage_account_id' => $storageAccount->id,
                    'room_id' => $room->id,
                    'user_id' => $user->id,
                    'filename' => $filename,
                    'size_bytes' => $sizeBytes,
                    'content_type' => $contentType,
                    ...$metadata,
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate Wasabi presigned URL', [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'filename' => $filename,
                'error' => $e->getMessage(),
                'storage_account_id' => $storageAccount->id ?? null,
            ]);

            throw new \Exception('Failed to generate upload URL: ' . $e->getMessage());
        }
    }

    private function validateFileConstraints(string $filename, string $contentType, int $sizeBytes): void
    {
        // Check file size (100MB max)
        $maxSize = 100 * 1024 * 1024; // 100MB
        if ($sizeBytes > $maxSize) {
            throw new \Exception('File size exceeds maximum allowed size of 100MB');
        }

        // Check content type (allow codec information after the base type)
        $allowedBaseTypes = ['video/webm', 'video/mp4', 'video/quicktime'];
        $isValidType = false;
        foreach ($allowedBaseTypes as $baseType) {
            if (str_starts_with($contentType, $baseType)) {
                $isValidType = true;
                break;
            }
        }
        
        if (!$isValidType) {
            throw new \Exception('File type not allowed. Only WebM, MP4, and QuickTime videos are supported.');
        }

        // Check filename
        if (empty($filename) || strlen($filename) > 255) {
            throw new \Exception('Invalid filename. Must be between 1 and 255 characters.');
        }

        // Basic security check - no path traversal
        if (str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\')) {
            throw new \Exception('Invalid filename. Path separators are not allowed.');
        }
    }
}

