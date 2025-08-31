<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Models\Room;
use Domain\User\Models\User;
use Exception;

class DeleteRoomAction
{
    public function execute(Room $room, User $user): void
    {
        // Only room creators can delete their rooms
        if (!$room->isCreator($user)) {
            throw new Exception('Only room creators can delete their rooms.');
        }

        // Delete the room (this will cascade delete participants)
        $room->delete();
    }
}
