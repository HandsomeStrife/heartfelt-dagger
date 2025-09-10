<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecording;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Log;

class CreateWasabiRecording
{
    public function execute(
        Room $room,
        User $user,
        string $providerFileId,
        string $filename,
        int $sizeBytes,
        int $startedAtMs,
        int $endedAtMs,
        string $mimeType = 'video/webm'
    ): RoomRecording {
        try {
            // Validate that recording is enabled for this room
            $room->load('recordingSettings');
            if (! $room->recordingSettings || ! $room->recordingSettings->isRecordingEnabled()) {
                throw new \Exception('Video recording is not enabled for this room');
            }

            // Validate storage provider
            if ($room->recordingSettings->storage_provider !== 'wasabi') {
                throw new \Exception('Room is not configured for Wasabi storage');
            }

            // Create recording record
            $recording = RoomRecording::create([
                'room_id' => $room->id,
                'user_id' => $user->id,
                'provider' => 'wasabi',
                'provider_file_id' => $providerFileId,
                'filename' => $filename,
                'size_bytes' => $sizeBytes,
                'started_at_ms' => $startedAtMs,
                'ended_at_ms' => $endedAtMs,
                'mime_type' => $mimeType,
                'status' => 'uploaded',
            ]);

            Log::info('Created Wasabi recording record', [
                'recording_id' => $recording->id,
                'room_id' => $room->id,
                'user_id' => $user->id,
                'provider_file_id' => $providerFileId,
                'filename' => $filename,
                'size_bytes' => $sizeBytes,
            ]);

            return $recording;

        } catch (\Exception $e) {
            Log::error('Failed to create Wasabi recording record', [
                'room_id' => $room->id,
                'user_id' => $user->id,
                'provider_file_id' => $providerFileId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
