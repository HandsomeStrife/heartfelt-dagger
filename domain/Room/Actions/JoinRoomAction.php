<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Character\Models\Character;
use Domain\Room\Data\RoomParticipantData;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;

class JoinRoomAction
{
    public function execute(
        Room $room,
        ?User $user,
        ?Character $character = null,
        ?string $temporaryCharacterName = null,
        ?string $temporaryCharacterClass = null
    ): RoomParticipantData {
        $userId = $user ? $user->id : 'anonymous';

        Log::info('JoinRoomAction - Starting execution', [
            'room_id' => $room->id,
            'user_id' => $userId,
            'character_id' => $character ? $character->id : null,
            'temporary_character_name' => $temporaryCharacterName,
            'temporary_character_class' => $temporaryCharacterClass,
        ]);

        // Check if authenticated user is already participating in this room
        if ($user && $room->hasActiveParticipant($user)) {
            Log::warning('JoinRoomAction - User already participating', [
                'room_id' => $room->id,
                'user_id' => $userId,
            ]);
            throw new Exception('You are already an active participant in this room.');
        }

        // Check if room is at capacity
        if ($room->isAtCapacity()) {
            Log::warning('JoinRoomAction - Room at capacity', [
                'room_id' => $room->id,
                'user_id' => $userId,
                'current_participants' => $room->getActiveParticipantCount(),
                'total_capacity' => $room->getTotalCapacity(),
            ]);
            throw new Exception('This room is at capacity.');
        }

        // Validate character ownership if provided
        if ($character && ($user === null || $character->user_id !== $user->id)) {
            Log::warning('JoinRoomAction - Character ownership validation failed', [
                'room_id' => $room->id,
                'user_id' => $userId,
                'character_id' => $character->id,
                'character_user_id' => $character->user_id,
            ]);
            throw new Exception('Character does not belong to the user.');
        }

        // Check if user has an existing participant record (including those who left)
        $existingParticipant = null;
        if ($user) {
            $existingParticipant = RoomParticipant::where('room_id', $room->id)
                ->where('user_id', $user->id)
                ->first();
        }

        if ($existingParticipant) {
            // Update existing participant to rejoin
            Log::info('JoinRoomAction - Updating existing participant record', [
                'room_id' => $room->id,
                'user_id' => $userId,
                'participant_id' => $existingParticipant->id,
                'character_id' => $character?->id,
                'character_name' => $temporaryCharacterName,
                'character_class' => $temporaryCharacterClass,
                'previous_left_at' => $existingParticipant->left_at,
            ]);

            $existingParticipant->update([
                'character_id' => $character?->id,
                'character_name' => $temporaryCharacterName,
                'character_class' => $temporaryCharacterClass,
                'joined_at' => now(),
                'left_at' => null, // Clear the left_at timestamp to make them active again
            ]);

            $participant = $existingParticipant;
        } else {
            // Create new participant record
            Log::info('JoinRoomAction - Creating new participant record', [
                'room_id' => $room->id,
                'user_id' => $userId,
                'character_id' => $character?->id,
                'character_name' => $temporaryCharacterName,
                'character_class' => $temporaryCharacterClass,
            ]);

            $participant = RoomParticipant::create([
                'room_id' => $room->id,
                'user_id' => $user?->id,
                'character_id' => $character?->id,
                'character_name' => $temporaryCharacterName,
                'character_class' => $temporaryCharacterClass,
                'joined_at' => now(),
            ]);
        }

        $participant->load(['user', 'character']);

        Log::info('JoinRoomAction - Participant created successfully', [
            'room_id' => $room->id,
            'user_id' => $userId,
            'participant_id' => $participant->id,
            'participant_user_id' => $participant->user_id,
            'participant_character_id' => $participant->character_id,
            'participant_character_name' => $participant->character_name,
            'participant_character_class' => $participant->character_class,
            'joined_at' => $participant->joined_at,
        ]);

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
