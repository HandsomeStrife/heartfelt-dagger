<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;

class UpdateSttConsent
{
    public function execute(Room $room, ?User $user, bool $consentGiven): RoomParticipant
    {
        // Find the participant record for this user in this room
        $participant = RoomParticipant::where('room_id', $room->id)
            ->where('user_id', $user?->id)
            ->whereNull('left_at')
            ->first();

        if (!$participant) {
            throw new \Exception('Participant not found in room or has already left');
        }

        // Update consent
        if ($consentGiven) {
            $participant->grantSttConsent();
        } else {
            $participant->denySttConsent();
        }

        return $participant;
    }

    public function executeForAnonymous(Room $room, string $sessionId, bool $consentGiven): RoomParticipant
    {
        // For anonymous users, we might need to track by session or another identifier
        // For now, we'll find participants without user_id that match some criteria
        $participant = RoomParticipant::where('room_id', $room->id)
            ->whereNull('user_id')
            ->whereNull('left_at')
            ->first(); // This is simplified - in production you'd need session tracking

        if (!$participant) {
            throw new \Exception('Anonymous participant not found in room');
        }

        // Update consent
        if ($consentGiven) {
            $participant->grantSttConsent();
        } else {
            $participant->denySttConsent();
        }

        return $participant;
    }
}
