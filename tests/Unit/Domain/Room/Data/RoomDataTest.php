<?php

declare(strict_types=1);
use Domain\Room\Data\RoomData;
use Domain\Room\Enums\RoomStatus;
use Domain\Room\Models\Room;
use Domain\User\Models\User;
use Livewire\Wireable;
use PHPUnit\Framework\Attributes\Test;
it('implements wireable interface', function () {
    $room = Room::factory()->create();
    $roomData = RoomData::from($room);

    expect($roomData)->toBeInstanceOf(Wireable::class);
});
it('creates from array', function () {
    $data = [
        'id' => 1,
        'name' => 'Test Room',
        'description' => 'A test room',
        'password' => 'hashed_password',
        'guest_count' => 4,
        'creator_id' => 1,
        'campaign_id' => null,
        'invite_code' => 'ABC12345',
        'viewer_code' => 'DEF67890',
        'status' => RoomStatus::Active,
        'created_at' => '2023-01-01 12:00:00',
        'updated_at' => '2023-01-01 12:00:00',
        'active_participant_count' => 3,
    ];

    $roomData = RoomData::from($data);

    expect($roomData->id)->toEqual(1);
    expect($roomData->name)->toEqual('Test Room');
    expect($roomData->description)->toEqual('A test room');
    expect($roomData->password)->toEqual('hashed_password');
    expect($roomData->guest_count)->toEqual(4);
    expect($roomData->creator_id)->toEqual(1);
    expect($roomData->invite_code)->toEqual('ABC12345');
    expect($roomData->status)->toEqual(RoomStatus::Active);
    expect($roomData->created_at)->toEqual('2023-01-01 12:00:00');
    expect($roomData->updated_at)->toEqual('2023-01-01 12:00:00');
    expect($roomData->active_participant_count)->toEqual(3);
});
it('creates from model', function () {
    $room = Room::factory()->create([
        'name' => 'Model Room',
        'description' => 'Created from model',
        'guest_count' => 2,
    ]);

    $roomData = RoomData::from($room);

    expect($roomData->id)->toEqual($room->id);
    expect($roomData->name)->toEqual('Model Room');
    expect($roomData->description)->toEqual('Created from model');
    expect($roomData->guest_count)->toEqual(2);
    expect($roomData->creator_id)->toEqual($room->creator_id);
    expect($roomData->invite_code)->toEqual($room->invite_code);
    expect($roomData->status)->toEqual($room->status);
});
it('creates from model with relationships', function () {
    $creator = User::factory()->create(['username' => 'room_creator']);
    $room = Room::factory()->create(['creator_id' => $creator->id]);
    $room->load('creator');

    $roomData = RoomData::from($room);

    expect($roomData->creator)->not->toBeNull();
    expect($roomData->creator->username)->toEqual('room_creator');
});
it('handles null optional fields', function () {
    $data = [
        'id' => null,
        'name' => 'Test Room',
        'description' => 'Description',
        'password' => 'password',
        'guest_count' => 1,
        'creator_id' => 1,
        'campaign_id' => null,
        'invite_code' => 'TESTCODE',
        'viewer_code' => 'VIEWCODE',
        'status' => RoomStatus::Active,
        'created_at' => null,
        'updated_at' => null,
        'creator' => null,
        'participants' => null,
        'active_participant_count' => null,
    ];

    $roomData = RoomData::from($data);

    expect($roomData->id)->toBeNull();
    expect($roomData->created_at)->toBeNull();
    expect($roomData->updated_at)->toBeNull();
    expect($roomData->creator)->toBeNull();
    expect($roomData->participants)->toBeNull();
    expect($roomData->active_participant_count)->toBeNull();
});
it('handles all status types', function () {
    foreach ([RoomStatus::Active, RoomStatus::Completed, RoomStatus::Archived] as $status) {
        $data = [
            'id' => 1,
            'name' => 'Test Room',
            'description' => 'Description',
            'password' => 'password',
            'guest_count' => 1,
            'creator_id' => 1,
            'campaign_id' => null,
            'invite_code' => 'TESTCODE',
            'viewer_code' => 'VIEWCODE',
            'status' => $status,
            'created_at' => '2023-01-01 12:00:00',
            'updated_at' => '2023-01-01 12:00:00',
        ];

        $roomData = RoomData::from($data);
        expect($roomData->status)->toEqual($status);
    }
});
it('creates with participant count from array', function () {
    $data = [
        'id' => 1,
        'name' => 'Test Room',
        'description' => 'Description',
        'password' => 'password',
        'guest_count' => 5,
        'creator_id' => 1,
        'campaign_id' => null,
        'invite_code' => 'TESTCODE',
        'viewer_code' => 'VIEWCODE',
        'status' => RoomStatus::Active,
        'created_at' => '2023-01-01 12:00:00',
        'updated_at' => '2023-01-01 12:00:00',
        'active_participant_count' => 3,
    ];

    $roomData = RoomData::from($data);

    expect($roomData->active_participant_count)->toEqual(3);
});
it('preserves string status from array', function () {
    $data = [
        'id' => 1,
        'name' => 'Test Room',
        'description' => 'Description',
        'password' => 'password',
        'guest_count' => 1,
        'creator_id' => 1,
        'campaign_id' => null,
        'invite_code' => 'TESTCODE',
        'viewer_code' => 'VIEWCODE',
        'status' => 'active', // String status
        'created_at' => '2023-01-01 12:00:00',
        'updated_at' => '2023-01-01 12:00:00',
    ];

    $roomData = RoomData::from($data);

    expect($roomData->status)->toEqual(RoomStatus::Active);
});
it('works with livewire to livewire', function () {
    $originalData = [
        'id' => 1,
        'name' => 'Livewire Room',
        'description' => 'Test livewire',
        'password' => 'password',
        'guest_count' => 3,
        'creator_id' => 1,
        'campaign_id' => null,
        'invite_code' => 'LIVEWIRE1',
        'viewer_code' => 'VIEWCODE',
        'status' => RoomStatus::Active,
        'created_at' => '2023-01-01 12:00:00',
        'updated_at' => '2023-01-01 12:00:00',
    ];

    $roomData = RoomData::from($originalData);
    $livewireArray = $roomData->toLivewire();
    $restoredData = RoomData::fromLivewire($livewireArray);

    expect($restoredData->id)->toEqual($roomData->id);
    expect($restoredData->name)->toEqual($roomData->name);
    expect($restoredData->description)->toEqual($roomData->description);
    expect($restoredData->guest_count)->toEqual($roomData->guest_count);
    expect($restoredData->status)->toEqual($roomData->status);
});
it('works with livewire from livewire', function () {
    $room = Room::factory()->create();
    $roomData = RoomData::from($room);

    $livewireArray = $roomData->toLivewire();
    $restoredData = RoomData::fromLivewire($livewireArray);

    expect($restoredData->id)->toEqual($roomData->id);
    expect($restoredData->name)->toEqual($roomData->name);
    expect($restoredData->guest_count)->toEqual($roomData->guest_count);
    expect($restoredData->invite_code)->toEqual($roomData->invite_code);
});
it('handles large participant counts', function () {
    $data = [
        'id' => 1,
        'name' => 'Big Room',
        'description' => 'Many participants',
        'password' => 'password',
        'guest_count' => 5,
        'creator_id' => 1,
        'campaign_id' => null,
        'invite_code' => 'BIGROOM1',
        'viewer_code' => 'VIEWCODE',
        'status' => RoomStatus::Active,
        'created_at' => '2023-01-01 12:00:00',
        'updated_at' => '2023-01-01 12:00:00',
        'active_participant_count' => 5,
    ];

    $roomData = RoomData::from($data);

    expect($roomData->active_participant_count)->toEqual(5);
    expect($roomData->guest_count)->toEqual(5);
});
it('handles zero participant count', function () {
    $data = [
        'id' => 1,
        'name' => 'Empty Room',
        'description' => 'No participants',
        'password' => 'password',
        'guest_count' => 3,
        'creator_id' => 1,
        'campaign_id' => null,
        'invite_code' => 'EMPTY001',
        'viewer_code' => 'VIEWCODE',
        'status' => RoomStatus::Active,
        'created_at' => '2023-01-01 12:00:00',
        'updated_at' => '2023-01-01 12:00:00',
        'active_participant_count' => 0,
    ];

    $roomData = RoomData::from($data);

    expect($roomData->active_participant_count)->toEqual(0);
});
it('handles different guest counts', function () {
    foreach ([1, 2, 3, 4, 5] as $guestCount) {
        $data = [
            'id' => $guestCount,
            'name' => "Room {$guestCount}",
            'description' => "Room for {$guestCount} guests",
            'password' => 'password',
            'guest_count' => $guestCount,
            'creator_id' => 1,
            'campaign_id' => null,
            'invite_code' => "ROOM{$guestCount}001",
            'viewer_code' => "VIEW{$guestCount}001",
            'status' => RoomStatus::Active,
            'created_at' => '2023-01-01 12:00:00',
            'updated_at' => '2023-01-01 12:00:00',
        ];

        $roomData = RoomData::from($data);
        expect($roomData->guest_count)->toEqual($guestCount);
    }
});
