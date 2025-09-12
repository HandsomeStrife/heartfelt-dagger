<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Enums\RecordingErrorType;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecording;
use Domain\Room\Models\RoomRecordingError;
use Domain\User\Models\User;

class LogRecordingError
{
    public function execute(
        Room $room,
        RecordingErrorType $errorType,
        string $errorMessage,
        ?User $user = null,
        ?RoomRecording $recording = null,
        ?string $errorCode = null,
        ?array $errorContext = null,
        ?string $provider = null,
        ?string $multipartUploadId = null,
        ?string $providerFileId = null
    ): RoomRecordingError {
        return RoomRecordingError::create([
            'room_id' => $room->id,
            'user_id' => $user?->id,
            'recording_id' => $recording?->id,
            'error_type' => $errorType->value,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'error_context' => $errorContext,
            'provider' => $provider,
            'multipart_upload_id' => $multipartUploadId,
            'provider_file_id' => $providerFileId,
            'occurred_at' => now(),
            'resolved' => false,
        ]);
    }
}
