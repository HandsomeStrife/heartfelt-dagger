<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Models\Room;
use Domain\User\Models\User;
use Exception;

class LeaveRoomAction
{
    public function execute(Room $room, User $user, ?User $removingUser = null): void
    {
        // If someone else is trying to remove this user, they must be the room creator
        if ($removingUser && $removingUser->id !== $user->id) {
            if (!$room->isCreator($removingUser)) {
                throw new Exception('Only the room creator can remove other participants.');
            }
        }

        // Prevent room creators from leaving their own rooms (unless being removed by themselves, which means deletion)
        if ($room->isCreator($user) && !$removingUser) {
            throw new Exception('Room creators cannot leave their own rooms. You can delete the room instead.');
        }

        // Find the active participant record
        $participant = $room->activeParticipants()
            ->where('user_id', $user->id)
            ->first();

        if (!$participant) {
            throw new Exception('This user is not an active participant in this room.');
        }

        // Mark the participant as having left
        $participant->update([
            'left_at' => now(),
        ]);
    }
}
