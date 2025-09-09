<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Data\SessionMarkerData;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecording;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CreateSessionMarkerForAllParticipants
{
    public function __construct(
        private CreateSessionMarker $createSessionMarker
    ) {}

    /**
     * Create session markers for all participants in a room
     *
     * @return Collection<SessionMarkerData>
     */
    public function execute(
        ?string $identifier,
        int $creatorId,
        int $roomId,
        ?int $videoTime = null,
        ?int $sttTime = null
    ): Collection {
        // Generate a shared UUID for all markers
        $uuid = Str::uuid()->toString();
        
        // Get the room with its participants
        $room = Room::with('participants.user')->findOrFail($roomId);
        
        // Find the current active recording for this room if any
        $recordingId = null;
        $activeRecording = RoomRecording::where('room_id', $roomId)
            ->where('status', 'recording')
            ->first();
        
        if ($activeRecording) {
            $recordingId = $activeRecording->id;
        }
        
        $markers = collect();
        
        // Create a marker for each participant (including the creator)
        $participantUserIds = $room->participants->pluck('user_id')->filter()->unique();
        
        foreach ($participantUserIds as $userId) {
            $marker = $this->createSessionMarker->execute(
                uuid: $uuid,
                identifier: $identifier,
                creatorId: $creatorId,
                userId: $userId,
                roomId: $roomId,
                recordingId: $recordingId,
                videoTime: $videoTime,
                sttTime: $sttTime
            );
            
            $markers->push($marker);
        }
        
        return $markers;
    }
}
