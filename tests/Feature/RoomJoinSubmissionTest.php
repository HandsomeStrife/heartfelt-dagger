<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\Room\Models\Room;
use Domain\User\Models\User;

use function Pest\Laravel\actingAs;

test('submitting form without any selection returns validation error', function () {
    $user = User::factory()->create();
    $room = Room::factory()->passwordless()->create();

    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Character',
        'class' => 'Warrior',
    ]);

    // Submit form without selecting anything (character_id empty, no temporary character info)
    $response = actingAs($user)->post(route('rooms.join', $room), [
        'character_id' => '', // Empty selection
        // No character_name or character_class
    ]);

    $response->assertSessionHasErrors(['character_name', 'character_class']);
    $response->assertRedirect();
});

test('submitting form with partial temporary character info returns validation error', function () {
    $user = User::factory()->create();
    $room = Room::factory()->passwordless()->create();

    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Character',
        'class' => 'Warrior',
    ]);

    // Submit form with only character name but no class
    $response = actingAs($user)->post(route('rooms.join', $room), [
        'character_id' => '', // Empty selection for temporary character
        'character_name' => 'Temp Character',
        // Missing character_class
    ]);

    $response->assertSessionHasErrors(['character_class']);
    $response->assertRedirect();
});

test('submitting form with valid existing character selection succeeds', function () {
    $user = User::factory()->create();
    $room = Room::factory()->passwordless()->create();

    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Selected Character',
        'class' => 'Bard',
    ]);

    // Submit form with existing character selected
    $response = actingAs($user)->post(route('rooms.join', $room), [
        'character_id' => $character->id,
    ]);

    $response->assertRedirect(route('rooms.session', $room));
    $response->assertSessionHas('success');

    assertDatabaseHas('room_participants', [
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => $character->id,
    ]);
});

test('submitting form with complete temporary character info succeeds', function () {
    $user = User::factory()->create();
    $room = Room::factory()->passwordless()->create();

    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Existing Character',
        'class' => 'Warrior',
    ]);

    // Submit form with complete temporary character info
    $response = actingAs($user)->post(route('rooms.join', $room), [
        'character_id' => '', // Empty for temporary character
        'character_name' => 'Temporary Character',
        'character_class' => 'Rogue',
    ]);

    $response->assertRedirect(route('rooms.session', $room));
    $response->assertSessionHas('success');

    assertDatabaseHas('room_participants', [
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_name' => 'Temporary Character',
        'character_class' => 'Rogue',
        'character_id' => null,
    ]);
});

test('room creator can access session directly without joining', function () {
    $user = User::factory()->create();
    $room = Room::factory()->passwordless()->create([
        'creator_id' => $user->id,
    ]);

    // Creator should be able to access session directly
    $response = actingAs($user)->get(route('rooms.session', $room));

    $response->assertOk();
    $response->assertViewIs('rooms.session');
    $response->assertViewHas('room');
});
