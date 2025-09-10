<?php

declare(strict_types=1);
use Domain\Room\Actions\LeaveRoomAction;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->action = new LeaveRoomAction;
});
it('allows participant to leave room', function () {
    $room = Room::factory()->create();
    $user = User::factory()->create();
    $participant = RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'left_at' => null,
    ]);

    expect($participant->left_at)->toBeNull();

    $this->action->execute($room, $user);

    $participant->refresh();
    expect($participant->left_at)->not->toBeNull();
});
it('sets left at timestamp', function () {
    $room = Room::factory()->create();
    $user = User::factory()->create();
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'left_at' => null,
    ]);

    $beforeLeave = now()->subSecond();
    // Add buffer for timing
    $this->action->execute($room, $user);
    $afterLeave = now()->addSecond();

    // Add buffer for timing
    $participant = RoomParticipant::where('room_id', $room->id)
        ->where('user_id', $user->id)
        ->first();

    expect($participant->left_at)->not->toBeNull();
    expect($participant->left_at->between($beforeLeave, $afterLeave))->toBeTrue();
});
it('prevents non participant from leaving', function () {
    $room = Room::factory()->create();
    $user = User::factory()->create();

    // User is not a participant
    expect(fn () => $this->action->execute($room, $user))
        ->toThrow(Exception::class, 'This user is not an active participant in this room.');
});
it('prevents already left participant from leaving again', function () {
    $room = Room::factory()->create();
    $user = User::factory()->create();
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'left_at' => now()->subHour(), // Already left
    ]);

    expect(fn () => $this->action->execute($room, $user))
        ->toThrow(Exception::class, 'This user is not an active participant in this room.');
});
it('allows multiple participants to leave independently', function () {
    $room = Room::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $participant1 = RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user1->id,
        'left_at' => null,
    ]);

    $participant2 = RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user2->id,
        'left_at' => null,
    ]);

    // User1 leaves
    $this->action->execute($room, $user1);

    $participant1->refresh();
    $participant2->refresh();

    expect($participant1->left_at)->not->toBeNull();
    expect($participant2->left_at)->toBeNull();

    // User2 can still leave
    $this->action->execute($room, $user2);

    $participant2->refresh();
    expect($participant2->left_at)->not->toBeNull();
});
it('handles participant in multiple rooms', function () {
    $room1 = Room::factory()->create();
    $room2 = Room::factory()->create();
    $user = User::factory()->create();

    $participant1 = RoomParticipant::factory()->create([
        'room_id' => $room1->id,
        'user_id' => $user->id,
        'left_at' => null,
    ]);

    $participant2 = RoomParticipant::factory()->create([
        'room_id' => $room2->id,
        'user_id' => $user->id,
        'left_at' => null,
    ]);

    // Leave room1 only
    $this->action->execute($room1, $user);

    $participant1->refresh();
    $participant2->refresh();

    expect($participant1->left_at)->not->toBeNull();
    expect($participant2->left_at)->toBeNull();
});
it('does not affect other participants when one leaves', function () {
    $room = Room::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $participant1 = RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user1->id,
        'left_at' => null,
    ]);

    $participant2 = RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user2->id,
        'left_at' => null,
    ]);

    $participant3 = RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user3->id,
        'left_at' => null,
    ]);

    // User2 leaves
    $this->action->execute($room, $user2);

    $participant1->refresh();
    $participant2->refresh();
    $participant3->refresh();

    expect($participant1->left_at)->toBeNull();
    expect($participant2->left_at)->not->toBeNull();
    expect($participant3->left_at)->toBeNull();
});
it('handles leaving with character attached', function () {
    $room = Room::factory()->create();
    $user = User::factory()->create();
    $participant = RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'left_at' => null,
    ]);

    expect($participant->character_id)->not->toBeNull();

    $this->action->execute($room, $user);

    $participant->refresh();
    expect($participant->left_at)->not->toBeNull();

    // Character association should remain for historical record
    expect($participant->character_id)->not->toBeNull();
});
it('handles leaving without character', function () {
    $room = Room::factory()->create();
    $user = User::factory()->create();
    $participant = RoomParticipant::factory()->withoutCharacter()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'left_at' => null,
    ]);

    $this->action->execute($room, $user);

    $participant->refresh();
    expect($participant->left_at)->not->toBeNull();
    expect($participant->character_id)->toBeNull();
});
it('handles leaving with temporary character', function () {
    $room = Room::factory()->create();
    $user = User::factory()->create();
    $participant = RoomParticipant::factory()->withTemporaryCharacter()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'left_at' => null,
        'character_name' => 'Temp Hero',
        'character_class' => 'Warrior',
    ]);

    $this->action->execute($room, $user);

    $participant->refresh();
    expect($participant->left_at)->not->toBeNull();

    // Temporary character info should remain for historical record
    expect($participant->character_name)->toEqual('Temp Hero');
    expect($participant->character_class)->toEqual('Warrior');
});
it('maintains room integrity after leave', function () {
    $room = Room::factory()->create();
    $user = User::factory()->create();
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'left_at' => null,
    ]);

    $originalRoomCount = Room::count();

    $this->action->execute($room, $user);

    // Room should still exist
    expect(Room::count())->toEqual($originalRoomCount);
    $room->refresh();
    expect($room)->not->toBeNull();
});
it('preserves participation record', function () {
    $room = Room::factory()->create();
    $user = User::factory()->create();
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'left_at' => null,
    ]);

    $originalParticipantCount = RoomParticipant::count();

    $this->action->execute($room, $user);

    // Participant record should still exist, just marked as left
    expect(RoomParticipant::count())->toEqual($originalParticipantCount);

    $participant = RoomParticipant::where('room_id', $room->id)
        ->where('user_id', $user->id)
        ->first();

    expect($participant)->not->toBeNull();
    expect($participant->left_at)->not->toBeNull();
});

it('prevents room creator from leaving their own room', function () {
    $creator = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $creator->id]);

    // Creator is automatically added as participant during room creation

    expect(fn () => $this->action->execute($room, $creator))
        ->toThrow(Exception::class, 'Room creators cannot leave their own rooms. You can delete the room instead.');
});
