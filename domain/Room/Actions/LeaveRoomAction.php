<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Models\Room;
use Domain\User\Models\User;
use Exception;

class LeaveRoomAction
{
    public function execute(Room $room, User $user): void
    {
        // Find the active participant record
        $participant = $room->activeParticipants()
            ->where('user_id', $user->id)
            ->first();

        if (!$participant) {
            throw new Exception('You are not an active participant in this room.');
        }

        // Mark the participant as having left
        $participant->update([
            'left_at' => now(),
        ]);
    }
}
