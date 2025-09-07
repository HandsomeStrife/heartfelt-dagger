<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Models\RoomRecording;

class UpdateRecordingProgress
{
    public function execute(
        RoomRecording $recording,
        int $partNumber,
        string $etag,
        int $partSizeBytes,
        int $endedAtMs
    ): RoomRecording {
        // Add the uploaded part
        $recording->addUploadedPart($partNumber, $etag);

        // Update the recording size and end time
        $recording->update([
            'size_bytes' => $recording->size_bytes + $partSizeBytes,
            'ended_at_ms' => $endedAtMs,
        ]);

        return $recording->fresh();
    }
}
