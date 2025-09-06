<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomSessionNotes;
use Domain\User\Models\User;

class LoadRoomSessionNotesAction
{
    /**
     * Load session notes for a room and user
     */
    public function execute(Room $room, User $user): RoomSessionNotes
    {
        return RoomSessionNotes::getOrCreateForRoomAndUser($room, $user);
    }
}
