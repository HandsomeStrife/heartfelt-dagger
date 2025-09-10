<?php

declare(strict_types=1);
use Domain\Room\Data\RoomData;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\Room\Repositories\RoomRepository;
use Domain\User\Models\User;
use PHPUnit\Framework\Attributes\Test;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->repository = new RoomRepository;
});
it('finds room by id with participant count', function () {
    $room = Room::factory()->create();
    RoomParticipant::factory()->count(3)->create(['room_id' => $room->id, 'left_at' => null]);
    RoomParticipant::factory()->create(['room_id' => $room->id, 'left_at' => now()]);

    // Inactive
    $foundRoom = $this->repository->findById($room->id);

    expect($foundRoom)->toBeInstanceOf(RoomData::class);
    expect($foundRoom->id)->toEqual($room->id);
    expect($foundRoom->active_participant_count)->toEqual(3);
    expect($foundRoom->creator)->not->toBeNull();
});
it('returns null for non existent room', function () {
    $foundRoom = $this->repository->findById(999);

    expect($foundRoom)->toBeNull();
});
it('finds room by invite code', function () {
    $room = Room::factory()->create();
    RoomParticipant::factory()->count(2)->create(['room_id' => $room->id, 'left_at' => null]);

    $foundRoom = $this->repository->findByInviteCode($room->invite_code);

    expect($foundRoom)->toBeInstanceOf(RoomData::class);
    expect($foundRoom->id)->toEqual($room->id);
    expect($foundRoom->invite_code)->toEqual($room->invite_code);
    expect($foundRoom->active_participant_count)->toEqual(2);
});
it('returns null for invalid invite code', function () {
    Room::factory()->create();

    $foundRoom = $this->repository->findByInviteCode('INVALID1');

    expect($foundRoom)->toBeNull();
});
it('gets rooms created by user', function () {
    $creator = User::factory()->create();
    $otherUser = User::factory()->create();

    $createdRooms = Room::factory()->count(3)->create(['creator_id' => $creator->id]);
    Room::factory()->count(2)->create(['creator_id' => $otherUser->id]);

    // Add participants to first room
    RoomParticipant::factory()->count(2)->create([
        'room_id' => $createdRooms->first()->id,
        'left_at' => null,
    ]);

    $foundRooms = $this->repository->getCreatedByUser($creator);

    expect($foundRooms)->toHaveCount(3);
    expect($foundRooms->every(fn ($room) => $room->creator_id === $creator->id))->toBeTrue();
    expect($foundRooms->first()->active_participant_count)->toEqual(2);
});
it('orders created rooms by newest first', function () {
    $user = User::factory()->create();

    $oldRoom = Room::factory()->create([
        'creator_id' => $user->id,
        'created_at' => now()->subDays(2),
    ]);
    $newRoom = Room::factory()->create([
        'creator_id' => $user->id,
        'created_at' => now(),
    ]);

    $foundRooms = $this->repository->getCreatedByUser($user);

    expect($foundRooms->first()->id)->toEqual($newRoom->id);
    expect($foundRooms->last()->id)->toEqual($oldRoom->id);
});
it('gets rooms joined by user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $joinedRoom = Room::factory()->create();
    $notJoinedRoom = Room::factory()->create();

    // User joins one room
    RoomParticipant::factory()->create([
        'room_id' => $joinedRoom->id,
        'user_id' => $user->id,
        'left_at' => null,
    ]);

    // Other user joins another room
    RoomParticipant::factory()->create([
        'room_id' => $notJoinedRoom->id,
        'user_id' => $otherUser->id,
        'left_at' => null,
    ]);

    $foundRooms = $this->repository->getJoinedByUser($user);

    expect($foundRooms)->toHaveCount(1);
    expect($foundRooms->first()->id)->toEqual($joinedRoom->id);
});
it('excludes left rooms from joined by user', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create();

    // User joined but then left
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'left_at' => now(),
    ]);

    $foundRooms = $this->repository->getJoinedByUser($user);

    expect($foundRooms)->toHaveCount(0);
});
it('gets room participants with relationships', function () {
    $room = Room::factory()->create();

    $participants = RoomParticipant::factory()->count(3)->create([
        'room_id' => $room->id,
        'left_at' => null,
    ]);

    // Add one who left
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'left_at' => now(),
    ]);

    $foundParticipants = $this->repository->getRoomParticipants($room);

    expect($foundParticipants)->toHaveCount(3);
    expect($foundParticipants->every(fn ($p) => $p->user !== null))->toBeTrue();
    expect($foundParticipants->every(fn ($p) => $p->character !== null))->toBeTrue();
});
it('orders participants by joined at ascending', function () {
    $room = Room::factory()->create();

    $laterParticipant = RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'joined_at' => now(),
        'left_at' => null,
    ]);

    $earlierParticipant = RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'joined_at' => now()->subHour(),
        'left_at' => null,
    ]);

    $foundParticipants = $this->repository->getRoomParticipants($room);

    expect($foundParticipants->first()->id)->toEqual($earlierParticipant->id);
    expect($foundParticipants->last()->id)->toEqual($laterParticipant->id);
});
it('includes participant count in all queries', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $user->id]);

    RoomParticipant::factory()->count(4)->create([
        'room_id' => $room->id,
        'left_at' => null,
    ]);

    // Test findById
    $foundById = $this->repository->findById($room->id);
    expect($foundById->active_participant_count)->toEqual(4);

    // Test findByInviteCode
    $foundByCode = $this->repository->findByInviteCode($room->invite_code);
    expect($foundByCode->active_participant_count)->toEqual(4);

    // Test getCreatedByUser
    $createdRooms = $this->repository->getCreatedByUser($user);
    expect($createdRooms->first()->active_participant_count)->toEqual(4);
});
it('handles rooms with zero participants', function () {
    $room = Room::factory()->create();

    // No participants added
    $foundRoom = $this->repository->findById($room->id);

    expect($foundRoom->active_participant_count)->toEqual(0);
});
it('returns empty collection for user with no rooms', function () {
    $user = User::factory()->create();
    Room::factory()->count(3)->create();

    // Other users' rooms
    $createdRooms = $this->repository->getCreatedByUser($user);
    $joinedRooms = $this->repository->getJoinedByUser($user);

    expect($createdRooms)->toHaveCount(0);
    expect($joinedRooms)->toHaveCount(0);
});
it('correctly counts mixed participant states', function () {
    $room = Room::factory()->create();

    // 3 active participants
    RoomParticipant::factory()->count(3)->create([
        'room_id' => $room->id,
        'left_at' => null,
    ]);

    // 2 participants who left
    RoomParticipant::factory()->count(2)->create([
        'room_id' => $room->id,
        'left_at' => now(),
    ]);

    $foundRoom = $this->repository->findById($room->id);

    expect($foundRoom->active_participant_count)->toEqual(3);
});
it('loads creator relationship in all methods', function () {
    $creator = User::factory()->create(['username' => 'unique_room_creator_789']);
    $room = Room::factory()->create(['creator_id' => $creator->id]);

    // Test findById
    $foundById = $this->repository->findById($room->id);
    expect($foundById->creator->username)->toEqual('unique_room_creator_789');

    // Test findByInviteCode
    $foundByCode = $this->repository->findByInviteCode($room->invite_code);
    expect($foundByCode->creator->username)->toEqual('unique_room_creator_789');

    // Test getCreatedByUser
    $createdRooms = $this->repository->getCreatedByUser($creator);
    expect($createdRooms->first()->creator->username)->toEqual('unique_room_creator_789');
});
