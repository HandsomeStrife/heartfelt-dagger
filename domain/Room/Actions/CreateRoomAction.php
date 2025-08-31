<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Data\CreateRoomData;
use Domain\Room\Data\RoomData;
use Domain\Room\Enums\RoomStatus;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;

class CreateRoomAction
{
    public function execute(CreateRoomData $createData, User $creator): RoomData
    {
        $room = Room::create([
            'name' => $createData->name,
            'description' => $createData->description,
            'password' => $createData->password ? bcrypt($createData->password) : null, // Hash only if password provided
            'guest_count' => $createData->guest_count,
            'creator_id' => $creator->id,
            'campaign_id' => $createData->campaign_id,
            'status' => RoomStatus::Active,
        ]);

        // Automatically add the creator as a participant
        RoomParticipant::create([
            'room_id' => $room->id,
            'user_id' => $creator->id,
            'character_id' => null, // Creator can join without a character initially
            'character_name' => null,
            'character_class' => null,
            'joined_at' => now(),
        ]);

        $room->load('creator');

        return RoomData::from([
            'id' => $room->id,
            'name' => $room->name,
            'description' => $room->description,
            'password' => $room->password, // This will be the hashed password
            'guest_count' => $room->guest_count,
            'creator_id' => $room->creator_id,
            'campaign_id' => $room->campaign_id,
            'invite_code' => $room->invite_code,
            'viewer_code' => $room->viewer_code,
            'status' => $room->status,
            'created_at' => $room->created_at?->toDateTimeString(),
            'updated_at' => $room->updated_at?->toDateTimeString(),
            'creator' => $room->creator,
            'participants' => collect(),
            'active_participant_count' => 1, // Now there's 1 participant (the creator)
        ]);
    }
}
