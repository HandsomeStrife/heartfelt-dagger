<?php

declare(strict_types=1);
use Domain\Room\Actions\CreateRoomAction;
use Domain\Room\Data\CreateRoomData;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->action = new CreateRoomAction;
});
it('creates room successfully', function () {
    $user = User::factory()->create();
    $createData = new CreateRoomData(
        name: 'Epic Adventure Room',
        description: 'A room for our weekly session',
        password: 'secret123',
        guest_count: 4
    );

    $roomData = $this->action->execute($createData, $user);

    expect($roomData->id)->not->toBeNull();
    expect($roomData->name)->toEqual('Epic Adventure Room');
    expect($roomData->description)->toEqual('A room for our weekly session');
    expect($roomData->guest_count)->toEqual(4);
    expect($roomData->creator_id)->toEqual($user->id);
    expect($roomData->invite_code)->not->toBeNull();
});
it('auto generates invite code', function () {
    $user = User::factory()->create();
    $createData = new CreateRoomData(
        name: 'Test Room',
        description: 'Test Description',
        password: 'password',
        guest_count: 2
    );

    $roomData = $this->action->execute($createData, $user);

    expect($roomData->invite_code)->not->toBeNull();
    expect(strlen($roomData->invite_code))->toEqual(8);
    expect($roomData->invite_code)->toMatch('/^[A-Z0-9]{8}$/');
});
it('hashes password', function () {
    $user = User::factory()->create();
    $createData = new CreateRoomData(
        name: 'Test Room',
        description: 'Test Description',
        password: 'plaintext',
        guest_count: 2
    );

    $roomData = $this->action->execute($createData, $user);

    // Password should be hashed, not plain text
    expect($roomData->password)->not->toEqual('plaintext');

    // Verify the password can be verified
    $room = Room::find($roomData->id);
    expect(password_verify('plaintext', $room->password))->toBeTrue();
});
it('persists room to database', function () {
    $user = User::factory()->create();
    $createData = new CreateRoomData(
        name: 'Persistent Room',
        description: 'This should be saved',
        password: 'test123',
        guest_count: 3
    );

    $roomData = $this->action->execute($createData, $user);

    \Pest\Laravel\assertDatabaseHas('rooms', [
        'id' => $roomData->id,
        'name' => 'Persistent Room',
        'description' => 'This should be saved',
        'guest_count' => 3,
        'creator_id' => $user->id,
        'invite_code' => $roomData->invite_code,
        'status' => 'active',
    ]);
});
it('loads creator relationship', function () {
    $user = User::factory()->create();
    $createData = new CreateRoomData(
        name: 'Test Room',
        description: 'Test Description',
        password: 'password',
        guest_count: 1
    );

    $roomData = $this->action->execute($createData, $user);

    expect($roomData->creator)->not->toBeNull();
    expect($roomData->creator->id)->toEqual($user->id);
    expect($roomData->creator->username)->toEqual($user->username);
});
it('creates rooms with unique codes', function () {
    $user = User::factory()->create();
    $createData1 = new CreateRoomData(
        name: 'Room 1',
        description: 'First room',
        password: 'password',
        guest_count: 2
    );
    $createData2 = new CreateRoomData(
        name: 'Room 2',
        description: 'Second room',
        password: 'password',
        guest_count: 3
    );

    $roomData1 = $this->action->execute($createData1, $user);
    $roomData2 = $this->action->execute($createData2, $user);

    expect($roomData1->invite_code)->not->toEqual($roomData2->invite_code);
});
it('handles different guest counts', function () {
    $user = User::factory()->create();

    foreach ([2, 3, 4, 5, 6] as $guestCount) {
        $createData = new CreateRoomData(
            name: "Room for {$guestCount}",
            description: "Room with {$guestCount} guests",
            password: 'password',
            guest_count: $guestCount
        );

        $roomData = $this->action->execute($createData, $user);

        expect($roomData->guest_count)->toEqual($guestCount);
    }
});
it('associates creator correctly', function () {
    $creator1 = User::factory()->create();
    $creator2 = User::factory()->create();

    $createData1 = new CreateRoomData(
        name: 'Creator 1 Room',
        description: 'Room by creator 1',
        password: 'password',
        guest_count: 2
    );

    $createData2 = new CreateRoomData(
        name: 'Creator 2 Room',
        description: 'Room by creator 2',
        password: 'password',
        guest_count: 3
    );

    $roomData1 = $this->action->execute($createData1, $creator1);
    $roomData2 = $this->action->execute($createData2, $creator2);

    expect($roomData1->creator_id)->toEqual($creator1->id);
    expect($roomData2->creator_id)->toEqual($creator2->id);
});
it('automatically adds creator as participant', function () {
    $user = User::factory()->create();
    $createData = new CreateRoomData(
        name: 'Room with Creator',
        description: 'Creator is automatically added as participant',
        password: 'password',
        guest_count: 5
    );

    $roomData = $this->action->execute($createData, $user);

    expect($roomData->active_participant_count)->toEqual(1);
});

it('creates room participant record for creator', function () {
    $user = User::factory()->create();
    $createData = new CreateRoomData(
        name: 'Test Room',
        description: 'Test Description',
        password: null,
        guest_count: 3
    );

    $roomData = $this->action->execute($createData, $user);

    // Check that a room participant record was created for the creator
    expect(RoomParticipant::where('room_id', $roomData->id)
        ->where('user_id', $user->id)
        ->whereNull('left_at')
        ->exists())->toBeTrue();
});

it('handles long names and descriptions', function () {
    $user = User::factory()->create();
    $createData = new CreateRoomData(
        name: str_repeat('A', 100), // Max length
        description: str_repeat('B', 500), // Max length
        password: 'password',
        guest_count: 2
    );

    $roomData = $this->action->execute($createData, $user);

    expect($roomData->name)->toEqual(str_repeat('A', 100));
    expect($roomData->description)->toEqual(str_repeat('B', 500));
});
