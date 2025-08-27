<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\Room\Models\Room;
use Domain\User\Models\User;
uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

test('user can complete full room creation workflow', function () {
    $user = User::factory()->create([
        'email' => 'creator@example.com',
        'password' => bcrypt('password'),
    ]);

    $page = visit("/rooms/{$room->id}");
    
    $page
                ->assertSee('Leave Test Room')
                ->assertSee('Leave Room')
                ->press('Leave Room')
                ->waitForLocation('/rooms')
                ->assertSee('Successfully left the room.');
});

test('room shows participant count and details', function () {
    $creator = User::factory()->create([
        'username' => 'room_creator',
    ]);

    $participant = User::factory()->create([
        'email' => 'participant@example.com',
        'password' => bcrypt('password'),
        'username' => 'test_participant',
    ]);

    $character = Character::factory()->create([
        'user_id' => $participant->id,
        'name' => 'Hero Character',
        'selected_class' => 'Ranger',
        'selected_subclass' => 'Beast Master',
        'selected_ancestry' => 'Elf',
    ]);

    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Participant Test Room',
        'guest_count' => 3,
    ]);

    // First, have participant join
    $page = visit('/rooms');
    
    $page
                ->assertSee('My Created Room')
                ->assertSee('Room I Joined')
                ->assertSee('My Rooms')
                ->assertSee('Joined Rooms');
});

test('session page loads webrtc functionality', function () {
    $user = User::factory()->create([
        'email' => 'webrtc@example.com',
        'password' => bcrypt('password'),
    ]);

    $room = Room::factory()->create([
        'creator_id' => $user->id,
        'name' => 'WebRTC Test Room',
    ]);

    $page = visit("/rooms/join/{$room->invite_code}");
    
    $page
                ->waitForLocation("/rooms/{$room->id}")
                ->assertSee('You are already participating in this room.');
});

test('room capacity enforcement works', function () {
    $creator = User::factory()->create();
    $user1 = User::factory()->create([
        'email' => 'user1@example.com',
        'password' => bcrypt('password'),
    ]);
    $user2 = User::factory()->create([
        'email' => 'user2@example.com',
        'password' => bcrypt('password'),
    ]);

    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Capacity Test Room',
        'guest_count' => 1, // Only 1 guest allowed
    ]);

    // First user joins successfully
    $page = visit('/');
    
    auth()->login($user1);\n    $page
                ->visit("/rooms/join/{$room->invite_code}")
                ->type('password', 'password')
                ->press('Join Room')
                ->waitForLocation("/rooms/{$room->id}/session")
                ->assertSee('Successfully joined the room!');
    });

    // Second user should be redirected due to capacity
    $page = visit('/');
    
    auth()->login($user2);\n    $page
                ->visit("/rooms/join/{$room->invite_code}")
                ->waitForLocation('/rooms')
                ->assertSee('This room is at capacity.');
});

test('room navigation works correctly', function () {
    $user = User::factory()->create([
        'email' => 'nav@example.com',
        'password' => bcrypt('password'),
    ]);

    $page = visit('/');
    
    auth()->login($user);\n    $page
                ->visit('/dashboard')
                ->click('Rooms')
                ->assertPathIs('/rooms')
                ->assertSee('Rooms')
                ->assertSee('Host and join live sessions')
                ->click('Create Room')
                ->assertPathIs('/rooms/create')
                ->assertSee('Create New Room');
