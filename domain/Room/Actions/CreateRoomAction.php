<?php

declare(strict_types=1);

namespace Domain\Room\Actions;

use Domain\Room\Data\CreateRoomData;
use Domain\Room\Data\RoomData;
use Domain\Room\Enums\RoomStatus;
use Domain\Room\Models\Room;
use Domain\User\Models\User;

class CreateRoomAction
{
    public function execute(CreateRoomData $createData, User $creator): RoomData
    {
        $room = Room::create([
            'name' => $createData->name,
            'description' => $createData->description,
            'password' => bcrypt($createData->password), // Hash the password for security
            'guest_count' => $createData->guest_count,
            'creator_id' => $creator->id,
            'status' => RoomStatus::Active,
        ]);

        $room->load('creator');

        return RoomData::from([
            'id' => $room->id,
            'name' => $room->name,
            'description' => $room->description,
            'password' => $room->password, // This will be the hashed password
            'guest_count' => $room->guest_count,
            'creator_id' => $room->creator_id,
            'invite_code' => $room->invite_code,
            'status' => $room->status,
            'created_at' => $room->created_at?->toDateTimeString(),
            'updated_at' => $room->updated_at?->toDateTimeString(),
            'creator' => $room->creator,
            'participants' => collect(),
            'active_participant_count' => 0,
        ]);
    }
}
