<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Models\Room;
use Domain\User\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;

class KickUserAction
{
    public function execute(Room $room, User $kickingUser, int $participantId): void
    {
        $kickingUserId = $kickingUser->id;
        
        Log::info('KickUserAction - Starting execution', [
            'room_id' => $room->id,
            'kicking_user_id' => $kickingUserId,
            'participant_id' => $participantId
        ]);
        
        // Only room creators can kick participants
        if (!$room->isCreator($kickingUser)) {
            Log::warning('KickUserAction - Non-creator tried to kick user', [
                'room_id' => $room->id,
                'kicking_user_id' => $kickingUserId,
                'participant_id' => $participantId
            ]);
            throw new Exception('Only the room creator can remove participants.');
        }

        // Find the participant to be kicked
        $participant = $room->activeParticipants()
            ->where('id', $participantId)
            ->first();

        if (!$participant) {
            Log::warning('KickUserAction - Participant not found or already inactive', [
                'room_id' => $room->id,
                'kicking_user_id' => $kickingUserId,
                'participant_id' => $participantId
            ]);
            throw new Exception('Participant not found or is no longer active in this room.');
        }

        // Prevent kicking the room creator (themselves)
        if ($participant->user_id === $room->creator_id) {
            Log::warning('KickUserAction - Attempted to kick room creator', [
                'room_id' => $room->id,
                'kicking_user_id' => $kickingUserId,
                'participant_id' => $participantId,
                'participant_user_id' => $participant->user_id
            ]);
            throw new Exception('The room creator cannot be removed from their own room.');
        }

        // Mark the participant as having left (kicked)
        $participant->update([
            'left_at' => now(),
        ]);
        
        Log::info('KickUserAction - Participant kicked successfully', [
            'room_id' => $room->id,
            'kicking_user_id' => $kickingUserId,
            'participant_id' => $participantId,
            'participant_user_id' => $participant->user_id,
            'participant_character_name' => $participant->character_name ?? ($participant->user ? $participant->user->username : 'Anonymous'),
            'kicked_at' => $participant->left_at
        ]);
    }
}
