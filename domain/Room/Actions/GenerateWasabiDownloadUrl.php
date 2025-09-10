<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecording;
use Domain\Room\Services\WasabiS3Service;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Log;

class GenerateWasabiDownloadUrl
{
    public function execute(
        Room $room,
        RoomRecording $recording,
        User $user,
        int $expirationMinutes = 60
    ): array {
        // Validate that user has access to this recording
        if (! $room->isCreator($user) && ! $room->hasActiveParticipant($user)) {
            throw new \Exception('Only room participants can download recordings');
        }

        // Validate that recording belongs to this room
        if ($recording->room_id !== $room->id) {
            throw new \Exception('Recording does not belong to this room');
        }

        // Validate storage provider
        if ($recording->provider !== 'wasabi') {
            throw new \Exception('Recording is not stored on Wasabi');
        }

        // Get the storage account
        $room->load('recordingSettings.storageAccount');
        $storageAccount = $room->recordingSettings?->storageAccount;

        if (! $storageAccount || $storageAccount->provider !== 'wasabi') {
            throw new \Exception('Wasabi storage account not found or invalid');
        }

        try {
            // Initialize Wasabi service
            $wasabiService = new WasabiS3Service($storageAccount);

            // Generate presigned download URL
            $downloadData = $wasabiService->generatePresignedDownloadUrl(
                $recording->provider_file_id,
                $expirationMinutes
            );

            Log::info('Generated Wasabi download URL', [
                'room_id' => $room->id,
                'recording_id' => $recording->id,
                'user_id' => $user->id,
                'key' => $recording->provider_file_id,
                'storage_account_id' => $storageAccount->id,
            ]);

            return [
                'success' => true,
                'download_url' => $downloadData['download_url'],
                'filename' => $recording->filename,
                'size_bytes' => $recording->size_bytes,
                'content_type' => $recording->mime_type,
                'expires_at' => $downloadData['expires_at'],
                'provider' => 'wasabi',
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate Wasabi download URL', [
                'room_id' => $room->id,
                'recording_id' => $recording->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'storage_account_id' => $storageAccount->id ?? null,
            ]);

            throw new \Exception('Failed to generate download URL: '.$e->getMessage());
        }
    }
}
