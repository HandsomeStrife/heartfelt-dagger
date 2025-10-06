<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Enums\RoomStatus;
use Domain\Room\Models\Room;
use Domain\User\Models\User;

class ArchiveRoomAction
{
    /**
     * Archive a room (only room creator can archive)
     */
    public function execute(Room $room, User $user): void
    {
        if (! $room->isCreator($user)) {
            throw new \Exception('Only the room creator can archive this room.');
        }

        if ($room->status === RoomStatus::Archived) {
            throw new \Exception('Room is already archived.');
        }

        // Update room status to archived
        $room->update(['status' => RoomStatus::Archived]);

        // Note: We don't remove participants or recordings/transcripts
        // Archived rooms preserve all content but can no longer be joined
    }
}
