<?php

declare(strict_types=1);
use Domain\Character\Models\Character;
use Domain\Room\Actions\JoinRoomAction;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->action = new JoinRoomAction;
});
it('joins room with character successfully', function () {
    $room = Room::factory()->create(['guest_count' => 5]);
    $user = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user->id]);

    $result = $this->action->execute($room, $user, $character);

    \Pest\Laravel\assertDatabaseHas('room_participants', [
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => $character->id,
        'character_name' => null,
        'character_class' => null,
    ]);

    expect($result->room_id)->toEqual($room->id);
    expect($result->user_id)->toEqual($user->id);
    expect($result->character_id)->toEqual($character->id);
});
it('joins room with temporary character successfully', function () {
    $room = Room::factory()->create(['guest_count' => 5]);
    $user = User::factory()->create();

    $result = $this->action->execute(
        $room,
        $user,
        null,
        'Gandalf',
        'Wizard'
    );

    \Pest\Laravel\assertDatabaseHas('room_participants', [
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => null,
        'character_name' => 'Gandalf',
        'character_class' => 'Wizard',
    ]);

    expect($result->room_id)->toEqual($room->id);
    expect($result->user_id)->toEqual($user->id);
    expect($result->character_id)->toBeNull();
    expect($result->character_name)->toEqual('Gandalf');
    expect($result->character_class)->toEqual('Wizard');
});
it('joins room without character', function () {
    $room = Room::factory()->create(['guest_count' => 5]);
    $user = User::factory()->create();

    $result = $this->action->execute($room, $user);

    \Pest\Laravel\assertDatabaseHas('room_participants', [
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => null,
        'character_name' => null,
        'character_class' => null,
    ]);

    expect($result->room_id)->toEqual($room->id);
    expect($result->user_id)->toEqual($user->id);
    expect($result->character_id)->toBeNull();
});
it('persists participation to database', function () {
    $room = Room::factory()->create();
    $user = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user->id]);

    $this->action->execute($room, $user, $character);

    \Pest\Laravel\assertDatabaseCount('room_participants', 1);
    $participant = RoomParticipant::first();

    expect($participant->room_id)->toEqual($room->id);
    expect($participant->user_id)->toEqual($user->id);
    expect($participant->character_id)->toEqual($character->id);
    expect($participant->joined_at)->not->toBeNull();
    expect($participant->left_at)->toBeNull();
});
it('sets joined at timestamp', function () {
    $room = Room::factory()->create();
    $user = User::factory()->create();

    $beforeJoin = now()->subSecond();
    // Add buffer for timing
    $result = $this->action->execute($room, $user);
    $afterJoin = now()->addSecond();

    // Add buffer for timing
    expect($result->joined_at)->not->toBeNull();
    $joinedAt = \Carbon\Carbon::parse($result->joined_at);
    expect($joinedAt->between($beforeJoin, $afterJoin))->toBeTrue();
});
it('loads all relationships', function () {
    $room = Room::factory()->create();
    $user = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user->id]);

    $result = $this->action->execute($room, $user, $character);

    expect($result->user)->not->toBeNull();
    expect($result->character)->not->toBeNull();
    expect($result->user->id)->toEqual($user->id);
    expect($result->character->id)->toEqual($character->id);
});
it('prevents duplicate participation', function () {
    $room = Room::factory()->create();
    $user = User::factory()->create();

    // Join once
    $this->action->execute($room, $user);

    // Try to join again
    expect(fn () => $this->action->execute($room, $user))
        ->toThrow(Exception::class, 'You are already an active participant in this room.');
});
it('prevents joining full room', function () {
    $room = Room::factory()->create(['guest_count' => 1]); // Total capacity = 2 (creator + 1 guest)
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    // Fill the room to capacity
    $this->action->execute($room, $user1); // 1 participant
    $this->action->execute($room, $user2); // 2 participants (at capacity)

    // Try to join full room
    expect(fn () => $this->action->execute($room, $user3))
        ->toThrow(Exception::class, 'This room is at capacity.');
});
it('validates character ownership', function () {
    $room = Room::factory()->create();
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherCharacter = Character::factory()->create(['user_id' => $otherUser->id]);

    expect(fn () => $this->action->execute($room, $user, $otherCharacter))
        ->toThrow(Exception::class, 'Character does not belong to the user.');
});
it('allows multiple users to join same room', function () {
    $room = Room::factory()->create(['guest_count' => 3]);
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $result1 = $this->action->execute($room, $user1);
    $result2 = $this->action->execute($room, $user2);

    \Pest\Laravel\assertDatabaseCount('room_participants', 2);
    expect($result1->user_id)->not->toEqual($result2->user_id);
});
it('allows user to join different rooms', function () {
    $room1 = Room::factory()->create();
    $room2 = Room::factory()->create();
    $user = User::factory()->create();

    $result1 = $this->action->execute($room1, $user);
    $result2 = $this->action->execute($room2, $user);

    \Pest\Laravel\assertDatabaseCount('room_participants', 2);
    expect($result1->room_id)->not->toEqual($result2->room_id);
});
it('handles null character gracefully', function () {
    $room = Room::factory()->create();
    $user = User::factory()->create();

    $result = $this->action->execute($room, $user, null);

    expect($result->character_id)->toBeNull();
    expect($result->character)->toBeNull();
});
it('maintains separate participations per room', function () {
    $room1 = Room::factory()->create();
    $room2 = Room::factory()->create();
    $user = User::factory()->create();
    $character1 = Character::factory()->create(['user_id' => $user->id]);
    $character2 = Character::factory()->create(['user_id' => $user->id]);

    $result1 = $this->action->execute($room1, $user, $character1);
    $result2 = $this->action->execute($room2, $user, $character2);

    expect($result1->character_id)->toEqual($character1->id);
    expect($result2->character_id)->toEqual($character2->id);
    \Pest\Laravel\assertDatabaseCount('room_participants', 2);
});
it('handles mixed character types', function () {
    $room = Room::factory()->create(['guest_count' => 3]);
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user1->id]);

    // Join with full character
    $result1 = $this->action->execute($room, $user1, $character);

    // Join with temporary character
    $result2 = $this->action->execute($room, $user2, null, 'Temp Hero', 'Ranger');

    // Join without character
    $result3 = $this->action->execute($room, $user3);

    expect($result1->character_id)->toEqual($character->id);
    expect($result2->character_id)->toBeNull();
    expect($result2->character_name)->toEqual('Temp Hero');
    expect($result3->character_id)->toBeNull();
    expect($result3->character_name)->toBeNull();
});
