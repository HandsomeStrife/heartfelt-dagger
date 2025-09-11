<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;

class KickParticipantAction
{
    public function execute(Room $room, User $kicker, int $participantId): bool
    {
        Log::info('KickParticipantAction - Starting execution', [
            'room_id' => $room->id,
            'kicker_id' => $kicker->id,
            'participant_id' => $participantId,
        ]);

        // Only room creator can kick participants
        if (!$room->isCreator($kicker)) {
            Log::warning('KickParticipantAction - Unauthorized kick attempt', [
                'room_id' => $room->id,
                'kicker_id' => $kicker->id,
                'participant_id' => $participantId,
            ]);
            throw new Exception('Only the room creator can kick participants.');
        }

        // Find the participant
        $participant = RoomParticipant::where('id', $participantId)
            ->where('room_id', $room->id)
            ->whereNull('left_at')
            ->first();

        if (!$participant) {
            Log::warning('KickParticipantAction - Participant not found or already left', [
                'room_id' => $room->id,
                'participant_id' => $participantId,
            ]);
            throw new Exception('Participant not found or has already left.');
        }

        // Cannot kick yourself
        if ($participant->user_id === $kicker->id) {
            throw new Exception('You cannot kick yourself from the room.');
        }

        // Mark participant as left (kicked)
        $participant->update([
            'left_at' => now(),
            'kick_reason' => 'Kicked by room creator',
        ]);

        Log::info('KickParticipantAction - Participant kicked successfully', [
            'room_id' => $room->id,
            'kicker_id' => $kicker->id,
            'participant_id' => $participantId,
            'kicked_user_id' => $participant->user_id,
        ]);

        return true;
    }
}
