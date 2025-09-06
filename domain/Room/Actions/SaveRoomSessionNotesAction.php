<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomSessionNotes;
use Domain\User\Models\User;

class SaveRoomSessionNotesAction
{
    /**
     * Save or update session notes for a room and user
     */
    public function execute(Room $room, User $user, string $notes): RoomSessionNotes
    {
        return RoomSessionNotes::updateNotesForRoomAndUser($room, $user, $notes);
    }
}
