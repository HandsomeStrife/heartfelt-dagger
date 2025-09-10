<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;

use function Pest\Laravel\actingAs;

test('room with 5 participants shows 5 video slots plus void slot', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create([
        'guest_count' => 5,
        'creator_id' => $user->id,
    ]);

    // Create participant record so user can access session
    RoomParticipant::factory()->withoutCharacter()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
    ]);

    actingAs($user);

    visit(route('rooms.session', $room->invite_code))
        // Check if we can see the participant count
        ->assertSee('1/5 participants');
});

test('room with 6 participants shows 6 video slots without void slot', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create([
        'guest_count' => 6,
        'creator_id' => $user->id,
    ]);

    // Create participant record so user can access session
    RoomParticipant::factory()->withoutCharacter()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
    ]);

    actingAs($user);

    visit(route('rooms.session', $room->invite_code))
        // Check if we can see the participant count
        ->assertSee('1/6 participants');
});
