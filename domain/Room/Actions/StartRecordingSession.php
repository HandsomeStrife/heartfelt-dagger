<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Enums\RecordingStatus;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecording;
use Domain\User\Models\User;

class StartRecordingSession
{
    public function execute(
        Room $room,
        User $user,
        string $filename,
        string $multipartUploadId,
        ?string $providerFileId,
        int $startedAtMs,
        string $mimeType = 'video/webm'
    ): RoomRecording {
        return RoomRecording::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'provider' => $room->recordingSettings->storage_provider,
            'provider_file_id' => $providerFileId,
            'multipart_upload_id' => $multipartUploadId,
            'filename' => $filename,
            'size_bytes' => 0,
            'started_at_ms' => $startedAtMs,
            'ended_at_ms' => $startedAtMs, // Will be updated as recording progresses
            'mime_type' => $mimeType,
            'status' => RecordingStatus::Recording,
            'uploaded_parts' => [],
        ]);
    }
}
