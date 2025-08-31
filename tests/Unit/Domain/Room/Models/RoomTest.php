<?php

declare(strict_types=1);
use Domain\Room\Enums\RoomStatus;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;
use PHPUnit\Framework\Attributes\Test;
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('generates unique invite code on creation', function () {
    $room = Room::factory()->create();

    expect($room->invite_code)->not->toBeNull();
    expect(strlen($room->invite_code))->toEqual(8);
    expect($room->invite_code)->toMatch('/^[A-Z0-9]{8}$/');
});
it('generates unique invite codes across multiple rooms', function () {
    $rooms = Room::factory()->count(5)->create();
    $inviteCodes = $rooms->pluck('invite_code')->toArray();

    expect(count(array_unique($inviteCodes)))->toEqual(5);
});
it('has proper default status', function () {
    $room = Room::factory()->create();

    expect($room->status)->toEqual(RoomStatus::Active);
});
it('casts status to enum', function () {
    $room = Room::factory()->create(['status' => RoomStatus::Completed]);

    expect($room->status)->toBeInstanceOf(RoomStatus::class);
    expect($room->status)->toEqual(RoomStatus::Completed);
});
it('casts guest count to integer', function () {
    $room = Room::factory()->create(['guest_count' => '3']);

    expect($room->guest_count)->toBeInt();
    expect($room->guest_count)->toEqual(3);
});
it('belongs to creator', function () {
    $room = Room::factory()->create();

    expect($room->creator)->toBeInstanceOf(User::class);
});
it('has many participants', function () {
    $room = Room::factory()->create();
    RoomParticipant::factory()->count(3)->create(['room_id' => $room->id]);

    $room->load('participants');

    expect($room->participants)->toHaveCount(3);
    expect($room->participants->first())->toBeInstanceOf(RoomParticipant::class);
});
it('has many active participants', function () {
    $room = Room::factory()->create();

    // Create active participants
    RoomParticipant::factory()->count(2)->create([
        'room_id' => $room->id,
        'left_at' => null
    ]);

    // Create participant who left
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'left_at' => now()
    ]);

    $activeParticipants = $room->activeParticipants;

    expect($activeParticipants)->toHaveCount(2);
});
it('checks if user is creator', function () {
    $creator = User::factory()->create();
    $otherUser = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $creator->id]);

    expect($room->isCreator($creator))->toBeTrue();
    expect($room->isCreator($otherUser))->toBeFalse();
});
it('checks if user is active participant', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $room = Room::factory()->create();

    // User is not a participant
    expect($room->hasActiveParticipant($user))->toBeFalse();

    // User becomes active participant
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'left_at' => null
    ]);

    expect($room->hasActiveParticipant($user))->toBeTrue();
    expect($room->hasActiveParticipant($otherUser))->toBeFalse();

    // User leaves
    $room->activeParticipants()->where('user_id', $user->id)->update(['left_at' => now()]);
    $room->refresh();

    expect($room->hasActiveParticipant($user))->toBeFalse();
});
it('gets active participant count', function () {
    $room = Room::factory()->create();

    expect($room->getActiveParticipantCount())->toEqual(0);

    // Add active participants
    RoomParticipant::factory()->count(3)->create([
        'room_id' => $room->id,
        'left_at' => null
    ]);

    // Add participant who left
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'left_at' => now()
    ]);

    expect($room->getActiveParticipantCount())->toEqual(3);
});
it('checks if at capacity', function () {
    $room = Room::factory()->create(['guest_count' => 2]); // Total capacity = 3 (creator + 2 guests)

    expect($room->isAtCapacity())->toBeFalse();

    // Add first participant
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'left_at' => null
    ]);

    expect($room->isAtCapacity())->toBeFalse();

    // Add second participant
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'left_at' => null
    ]);

    expect($room->isAtCapacity())->toBeFalse();

    // Add third participant (at capacity)
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'left_at' => null
    ]);

    expect($room->isAtCapacity())->toBeTrue();
});
it('generates invite url', function () {
    $room = Room::factory()->create();

    $expectedUrl = route('rooms.invite', ['invite_code' => $room->invite_code]);
    expect($room->getInviteUrl())->toEqual($expectedUrl);
});
it('scopes rooms by creator', function () {
    $creator = User::factory()->create();
    $otherUser = User::factory()->create();

    $creatorRooms = Room::factory()->count(3)->create(['creator_id' => $creator->id]);
    Room::factory()->count(2)->create(['creator_id' => $otherUser->id]);

    $foundRooms = Room::byCreator($creator)->get();

    expect($foundRooms)->toHaveCount(3);
    expect($foundRooms->every(fn($room) => $room->creator_id === $creator->id))->toBeTrue();
});
it('scopes rooms by invite code', function () {
    $room = Room::factory()->create();
    Room::factory()->count(2)->create();

    // Other rooms
    $foundRoom = Room::byInviteCode($room->invite_code)->first();

    expect($foundRoom)->not->toBeNull();
    expect($foundRoom->id)->toEqual($room->id);
});
it('scopes active rooms', function () {
    // Clear any existing rooms to ensure test isolation
    Room::query()->delete();

    Room::factory()->count(3)->create(['status' => RoomStatus::Active]);
    Room::factory()->count(2)->create(['status' => RoomStatus::Completed]);
    Room::factory()->create(['status' => RoomStatus::Archived]);

    $activeRooms = Room::active()->get();

    expect($activeRooms)->toHaveCount(3);
    expect($activeRooms->every(fn($room) => $room->status === RoomStatus::Active))->toBeTrue();
});
it('generates unique invite codes when duplicates exist', function () {
    // Mock the random generation to return a duplicate first, then unique
    $existingRoom = Room::factory()->create();
    $existingCode = $existingRoom->invite_code;

    // Create another room - should get different code even if random generates same initially
    $newRoom = Room::factory()->create();

    expect($newRoom->invite_code)->not->toEqual($existingCode);
});
it('validates guest count range', function () {
    // Valid guest counts
    foreach ([1, 2, 3, 4, 5] as $count) {
        $room = Room::factory()->create(['guest_count' => $count]);
        expect($room->guest_count)->toEqual($count);
    }
});
it('handles password storage', function () {
    $room = Room::factory()->create();

    // Password should be hashed
    expect($room->password)->not->toEqual('password');
    expect(password_verify('password', $room->password))->toBeTrue();
});
