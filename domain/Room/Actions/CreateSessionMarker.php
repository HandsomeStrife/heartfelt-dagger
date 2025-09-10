<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Data\SessionMarkerData;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecording;
use Domain\Room\Models\SessionMarker;
use Domain\User\Models\User;

class CreateSessionMarker
{
    public function execute(
        string $uuid,
        ?string $identifier,
        int $creatorId,
        int $userId,
        int $roomId,
        ?int $recordingId = null,
        ?int $videoTime = null,
        ?int $sttTime = null
    ): SessionMarkerData {
        // Validate that the room exists
        $room = Room::findOrFail($roomId);

        // Validate that the creator exists
        $creator = User::findOrFail($creatorId);

        // Validate that the user exists
        $user = User::findOrFail($userId);

        // Validate recording if provided
        if ($recordingId) {
            $recording = RoomRecording::where('id', $recordingId)
                ->where('room_id', $roomId)
                ->firstOrFail();
        }

        // Create the session marker
        $marker = SessionMarker::create([
            'uuid' => $uuid,
            'identifier' => $identifier,
            'creator_id' => $creatorId,
            'user_id' => $userId,
            'room_id' => $roomId,
            'recording_id' => $recordingId,
            'video_time' => $videoTime,
            'stt_time' => $sttTime,
        ]);

        return SessionMarkerData::fromModel($marker);
    }
}
