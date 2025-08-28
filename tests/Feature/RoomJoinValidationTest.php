<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};
use Domain\User\Models\User;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};

test('joining room without character requires name and class', function () {
    $user = User::factory()->create();
    $room = Room::factory()->passwordless()->create();

    // Joining without character_id but also without character_name should fail
    $response = actingAs($user)->post(route('rooms.join', $room), []);

    $response->assertSessionHasErrors(['character_name', 'character_class']);
});

test('joining room without character requires valid class', function () {
    $user = User::factory()->create();
    $room = Room::factory()->passwordless()->create();

    // Joining with invalid character class should fail
    $response = actingAs($user)->post(route('rooms.join', $room), [
        'character_name' => 'Test Character',
        'character_class' => 'InvalidClass'
    ]);

    $response->assertSessionHasErrors(['character_class']);
});

test('joining room with valid temporary character succeeds', function () {
    $user = User::factory()->create();
    $room = Room::factory()->passwordless()->create();

    // Joining with valid temporary character should succeed
    $response = actingAs($user)->post(route('rooms.join', $room), [
        'character_name' => 'Valid Character',
        'character_class' => 'Warrior'
    ]);

    $response->assertRedirect(route('rooms.session', $room));
    
    assertDatabaseHas('room_participants', [
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_name' => 'Valid Character',
        'character_class' => 'Warrior'
    ]);
});

test('joining room with existing character does not require name and class', function () {
    $user = User::factory()->create();
    $room = Room::factory()->passwordless()->create();
    $character = \Domain\Character\Models\Character::factory()->create(['user_id' => $user->id]);

    // Joining with existing character should not require name/class
    $response = actingAs($user)->post(route('rooms.join', $room), [
        'character_id' => $character->id
    ]);

    $response->assertRedirect(route('rooms.session', $room));
    
    assertDatabaseHas('room_participants', [
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => $character->id
    ]);
});
