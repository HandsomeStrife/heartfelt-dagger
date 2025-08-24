<?php

uses(\Tests\DuskTestCase::class);
declare(strict_types=1);
use Domain\Character\Models\Character;
use Domain\Room\Models\Room;
use Domain\User\Models\User;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

test('user can complete full room creation workflow', function () {
    $user = User::factory()->create([
        'email' => 'creator@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
                ->visit('/rooms')
                ->assertSee('Rooms')
                ->clickLink('Create Room')
                ->assertPathIs('/rooms/create')
                ->type('name', 'Epic Adventure Room')
                ->type('description', 'A room for our weekly D&D session')
                ->type('password', 'secret123')
                ->select('guest_count', '4')
                ->press('Create Room')
                ->waitForText('Epic Adventure Room')
                ->assertSee('Room created successfully!')
                ->assertSee('Epic Adventure Room')
                ->assertSee('A room for our weekly D&D session')
                ->assertSee('Max Guests: 4');
    });
});
test('user can copy invite link and view room details', function () {
    $creator = User::factory()->create([
        'email' => 'creator@example.com',
        'password' => bcrypt('password'),
    ]);

    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Test Room',
        'description' => 'Test room for copying invite',
        'guest_count' => 3,
    ]);

    $this->browse(function (Browser $browser) use ($creator, $room) {
        $browser->loginAs($creator)
                ->visit("/rooms/{$room->id}")
                ->assertSee('Test Room')
                ->assertSee('Test room for copying invite')
                ->assertSee('Max Guests: 3')
                ->assertSee($room->invite_code)
                ->assertSee('Copy Invite Link')
                ->assertSee('Start Session'); // Creator should see start button
    });
});
test('second user can join room via invite link', function () {
    $creator = User::factory()->create([
        'email' => 'creator@example.com',
        'password' => bcrypt('password'),
    ]);

    $joiner = User::factory()->create([
        'email' => 'joiner@example.com',
        'password' => bcrypt('password'),
        'username' => 'joiner_user',
    ]);

    $character = Character::factory()->create([
        'user_id' => $joiner->id,
        'name' => 'Test Hero',
        'selected_class' => 'Warrior',
    ]);

    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Join Test Room',
        'guest_count' => 5,
    ]);

    $this->browse(function (Browser $browser) use ($joiner, $room, $character) {
        $browser->loginAs($joiner)
                ->visit("/rooms/join/{$room->invite_code}")
                ->assertSee('Join Room: Join Test Room')
                ->type('password', 'password') // Factory default
                ->select('character_id', $character->id)
                ->press('Join Room')
                ->waitForLocation("/rooms/{$room->id}/session")
                ->assertSee('Successfully joined the room!');
    });
});
test('user can join room with temporary character', function () {
    $creator = User::factory()->create();
    $joiner = User::factory()->create([
        'email' => 'joiner@example.com',
        'password' => bcrypt('password'),
    ]);

    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Temp Character Room',
    ]);

    $this->browse(function (Browser $browser) use ($joiner, $room) {
        $browser->loginAs($joiner)
                ->visit("/rooms/join/{$room->invite_code}")
                ->assertSee('Join Room: Temp Character Room')
                ->type('password', 'password')
                ->type('character_name', 'Gandalf')
                ->type('character_class', 'Wizard')
                ->press('Join Room')
                ->waitForLocation("/rooms/{$room->id}/session")
                ->assertSee('Successfully joined the room!');
    });
});
test('room creator can start session', function () {
    $creator = User::factory()->create([
        'email' => 'creator@example.com',
        'password' => bcrypt('password'),
    ]);

    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Creator Session Room',
    ]);

    $this->browse(function (Browser $browser) use ($creator, $room) {
        $browser->loginAs($creator)
                ->visit("/rooms/{$room->id}")
                ->assertSee('Creator Session Room')
                ->clickLink('Start Session')
                ->waitForLocation("/rooms/{$room->id}/session")
                ->assertSee('Creator Session Room'); // Should be in session page
    });
});
test('user can leave room successfully', function () {
    $creator = User::factory()->create();
    $participant = User::factory()->create([
        'email' => 'participant@example.com',
        'password' => bcrypt('password'),
    ]);

    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Leave Test Room',
    ]);

    // First join the room
    $this->browse(function (Browser $browser) use ($participant, $room) {
        $browser->loginAs($participant)
                ->visit("/rooms/join/{$room->invite_code}")
                ->type('password', 'password')
                ->press('Join Room')
                ->waitForLocation("/rooms/{$room->id}/session");
    });

    // Then leave the room
    $this->browse(function (Browser $browser) use ($participant, $room) {
        $browser->visit("/rooms/{$room->id}")
                ->assertSee('Leave Test Room')
                ->assertSee('Leave Room')
                ->press('Leave Room')
                ->waitForLocation('/rooms')
                ->assertSee('Successfully left the room.');
    });
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
    $this->browse(function (Browser $browser) use ($participant, $room, $character) {
        $browser->loginAs($participant)
                ->visit("/rooms/join/{$room->invite_code}")
                ->type('password', 'password')
                ->select('character_id', $character->id)
                ->press('Join Room')
                ->waitForLocation("/rooms/{$room->id}/session");
    });

    // Then check room details show participant info
    $this->browse(function (Browser $browser) use ($creator, $room) {
        $browser->loginAs($creator)
                ->visit("/rooms/{$room->id}")
                ->assertSee('Participants (1/4)') // 1 participant + creator capacity
                ->assertSee('Hero Character')
                ->assertSee('Ranger')
                ->assertSee('Beast Master')
                ->assertSee('test_participant');
    });
});
test('user cannot join room with wrong password', function () {
    $creator = User::factory()->create();
    $joiner = User::factory()->create([
        'email' => 'joiner@example.com',
        'password' => bcrypt('password'),
    ]);

    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Password Protected Room',
    ]);

    $this->browse(function (Browser $browser) use ($joiner, $room) {
        $browser->loginAs($joiner)
                ->visit("/rooms/join/{$room->invite_code}")
                ->assertSee('Join Room: Password Protected Room')
                ->type('password', 'wrong_password')
                ->press('Join Room')
                ->waitForText('Invalid room password.')
                ->assertSee('Invalid room password.');
    });
});
test('room dashboard shows created and joined rooms', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('password'),
    ]);

    $createdRoom = Room::factory()->create([
        'creator_id' => $user->id,
        'name' => 'My Created Room',
    ]);

    $otherCreator = User::factory()->create();
    $joinedRoom = Room::factory()->create([
        'creator_id' => $otherCreator->id,
        'name' => 'Room I Joined',
    ]);

    // Join the second room
    $this->browse(function (Browser $browser) use ($user, $joinedRoom) {
        $browser->loginAs($user)
                ->visit("/rooms/join/{$joinedRoom->invite_code}")
                ->type('password', 'password')
                ->press('Join Room')
                ->waitForLocation("/rooms/{$joinedRoom->id}/session");
    });

    // Check dashboard shows both rooms
    $this->browse(function (Browser $browser) use ($user) {
        $browser->visit('/rooms')
                ->assertSee('My Created Room')
                ->assertSee('Room I Joined')
                ->assertSee('My Rooms')
                ->assertSee('Joined Rooms');
    });
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

    $this->browse(function (Browser $browser) use ($user, $room) {
        $browser->loginAs($user)
                ->visit("/rooms/{$room->id}/session")
                ->assertSourceHas('window.roomData')
                ->assertSourceHas('window.currentUserId')
                ->assertSourceHas('room-webrtc.js')
                ->assertSee('WebRTC Test Room');
    });
});
test('invalid invite code shows 404', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
                ->visit('/rooms/join/INVALID1')
                ->assertSee('404');
    });
});
test('user already in room redirected from invite', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('password'),
    ]);

    $room = Room::factory()->create([
        'creator_id' => $user->id,
        'name' => 'Already Joined Room',
    ]);

    // Join the room first
    $this->browse(function (Browser $browser) use ($user, $room) {
        $browser->loginAs($user)
                ->visit("/rooms/join/{$room->invite_code}")
                ->type('password', 'password')
                ->press('Join Room')
                ->waitForLocation("/rooms/{$room->id}/session");
    });

    // Try to visit invite again
    $this->browse(function (Browser $browser) use ($user, $room) {
        $browser->visit("/rooms/join/{$room->invite_code}")
                ->waitForLocation("/rooms/{$room->id}")
                ->assertSee('You are already participating in this room.');
    });
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
    $this->browse(function (Browser $browser) use ($user1, $room) {
        $browser->loginAs($user1)
                ->visit("/rooms/join/{$room->invite_code}")
                ->type('password', 'password')
                ->press('Join Room')
                ->waitForLocation("/rooms/{$room->id}/session")
                ->assertSee('Successfully joined the room!');
    });

    // Second user should be redirected due to capacity
    $this->browse(function (Browser $browser) use ($user2, $room) {
        $browser->loginAs($user2)
                ->visit("/rooms/join/{$room->invite_code}")
                ->waitForLocation('/rooms')
                ->assertSee('This room is at capacity.');
    });
});
test('room navigation works correctly', function () {
    $user = User::factory()->create([
        'email' => 'nav@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
                ->visit('/dashboard')
                ->clickLink('Rooms')
                ->assertPathIs('/rooms')
                ->assertSee('Rooms')
                ->assertSee('Host and join live sessions')
                ->clickLink('Create Room')
                ->assertPathIs('/rooms/create')
                ->assertSee('Create New Room');
    });
});
