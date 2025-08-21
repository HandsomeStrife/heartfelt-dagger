<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Character\Models\Character;
use Domain\Room\Data\RoomParticipantData;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;
use Exception;

class JoinRoomAction
{
    public function execute(
        Room $room,
        User $user,
        ?Character $character = null,
        ?string $temporaryCharacterName = null,
        ?string $temporaryCharacterClass = null
    ): RoomParticipantData {
        // Check if user is already participating in this room
        if ($room->hasActiveParticipant($user)) {
            throw new Exception('You are already an active participant in this room.');
        }

        // Check if room is at capacity
        if ($room->isAtCapacity()) {
            throw new Exception('This room is at capacity.');
        }

        // Validate character ownership if provided
        if ($character && $character->user_id !== $user->id) {
            throw new Exception('Character does not belong to the user.');
        }

        // Create the room participant
        $participant = RoomParticipant::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'character_id' => $character?->id,
            'character_name' => $temporaryCharacterName,
            'character_class' => $temporaryCharacterClass,
            'joined_at' => now(),
        ]);

        $participant->load(['user', 'character']);

        return RoomParticipantData::from([
            'id' => $participant->id,
            'room_id' => $participant->room_id,
            'user_id' => $participant->user_id,
            'character_id' => $participant->character_id,
            'character_name' => $participant->character_name,
            'character_class' => $participant->character_class,
            'joined_at' => $participant->joined_at?->toDateTimeString(),
            'left_at' => $participant->left_at?->toDateTimeString(),
            'created_at' => $participant->created_at?->toDateTimeString(),
            'updated_at' => $participant->updated_at?->toDateTimeString(),
            'user' => $participant->user,
            'character' => $participant->character,
        ]);
    }
}
