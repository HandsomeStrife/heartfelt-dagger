<?php

declare(strict_types=1);

use Domain\Room\Actions\DeleteRoomAction;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->action = new DeleteRoomAction;
});

it('allows room creator to delete their room', function () {
    $creator = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $creator->id]);

    // Add some participants
    RoomParticipant::factory()->count(2)->create(['room_id' => $room->id]);

    $roomId = $room->id;
    $participantCount = RoomParticipant::where('room_id', $roomId)->count();

    expect($participantCount)->toBeGreaterThan(0);
    expect(Room::find($roomId))->not->toBeNull();

    $this->action->execute($room, $creator);

    // Room should be deleted
    expect(Room::find($roomId))->toBeNull();

    // Participants should be deleted due to cascade
    expect(RoomParticipant::where('room_id', $roomId)->count())->toEqual(0);
});

it('prevents non-creator from deleting room', function () {
    $creator = User::factory()->create();
    $otherUser = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $creator->id]);

    expect(fn () => $this->action->execute($room, $otherUser))
        ->toThrow(Exception::class, 'Only room creators can delete their rooms.');
});

it('deletes room with multiple participants', function () {
    $creator = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $creator->id]);

    // Add multiple participants
    $users = User::factory()->count(3)->create();
    foreach ($users as $user) {
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
        ]);
    }

    $roomId = $room->id;
    expect(RoomParticipant::where('room_id', $roomId)->count())->toEqual(3);

    $this->action->execute($room, $creator);

    // All participants should be deleted
    expect(RoomParticipant::where('room_id', $roomId)->count())->toEqual(0);
    expect(Room::find($roomId))->toBeNull();
});

it('deletes room with mixed participant states', function () {
    $creator = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $creator->id]);

    // Create active and left participants
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'left_at' => null, // Active
    ]);

    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'left_at' => now()->subHour(), // Left
    ]);

    $roomId = $room->id;
    expect(RoomParticipant::where('room_id', $roomId)->count())->toEqual(2);

    $this->action->execute($room, $creator);

    // All participants should be deleted regardless of status
    expect(RoomParticipant::where('room_id', $roomId)->count())->toEqual(0);
    expect(Room::find($roomId))->toBeNull();
});

it('does not affect other rooms when deleting one', function () {
    $creator = User::factory()->create();
    $room1 = Room::factory()->create(['creator_id' => $creator->id]);
    $room2 = Room::factory()->create(); // Different creator

    RoomParticipant::factory()->create(['room_id' => $room1->id]);
    RoomParticipant::factory()->create(['room_id' => $room2->id]);

    $room1Id = $room1->id;
    $room2Id = $room2->id;

    $this->action->execute($room1, $creator);

    // Room 1 should be deleted
    expect(Room::find($room1Id))->toBeNull();
    expect(RoomParticipant::where('room_id', $room1Id)->count())->toEqual(0);

    // Room 2 should remain intact
    expect(Room::find($room2Id))->not->toBeNull();
    expect(RoomParticipant::where('room_id', $room2Id)->count())->toEqual(1);
});
