<?php

declare(strict_types=1);
use Domain\Campaign\Models\Campaign;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};
use Domain\Campaign\Models\CampaignMember;
use Domain\Character\Models\Character;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use function Pest\Laravel\{assertDatabaseHas, assertDatabaseMissing, assertDatabaseCount};
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guest cannot access room routes', function () {
    $room = Room::factory()->create();

    get(route('rooms.index'))->assertRedirect(route('login'));
    get(route('rooms.create'))->assertRedirect(route('login'));
    post(route('rooms.store'))->assertRedirect(route('login'));
    get(route('rooms.show', $room))->assertRedirect(route('login'));
    post(route('rooms.join', $room))->assertRedirect(route('login'));
    delete(route('rooms.leave', $room))->assertRedirect(route('login'));
    get(route('rooms.session', $room))->assertRedirect(route('login'));
});
test('guest can access invite route', function () {
    $room = Room::factory()->create();

    $response = get(route('rooms.invite', $room->invite_code));

    $response->assertRedirect(route('login'));
});
test('authenticated user can view rooms index', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->get(route('rooms.index'));

    $response->assertOk();
    $response->assertViewIs('rooms.index');
    $response->assertViewHas(['created_rooms', 'joined_rooms']);
});
test('rooms index displays created and joined rooms', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    // Rooms created by user
    $createdRoom = Room::factory()->create(['creator_id' => $user->id, 'name' => 'My Room']);

    // Rooms joined by user
    $joinedRoom = Room::factory()->create(['creator_id' => $otherUser->id, 'name' => 'Joined Room']);
    RoomParticipant::factory()->withoutCharacter()->create([
        'room_id' => $joinedRoom->id,
        'user_id' => $user->id,
        'left_at' => null,
        'character_id' => null,
    ]);

    // Other room not related to user
    $otherRoom = Room::factory()->create(['creator_id' => $otherUser->id, 'name' => 'Other Room']);

    $response = actingAs($user)->get(route('rooms.index'));

    $response->assertSee('My Room');
    $response->assertSee('Joined Room');
    $response->assertDontSee('Other Room');
});
test('authenticated user can view create room form', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->get(route('rooms.create'));

    $response->assertOk();
    $response->assertViewIs('rooms.create');
});
test('user can create room with valid data', function () {
    $user = User::factory()->create();

    $roomData = [
        'name' => 'Epic Adventure Room',
        'description' => 'A room for our weekly session',
        'password' => 'secret123',
        'guest_count' => 4
    ];

    $response = actingAs($user)->post(route('rooms.store'), $roomData);

    $response->assertRedirect();
    assertDatabaseHas('rooms', [
        'name' => 'Epic Adventure Room',
        'description' => 'A room for our weekly session',
        'guest_count' => 4,
        'creator_id' => $user->id,
        'status' => 'active'
    ]);

    $room = Room::where('name', 'Epic Adventure Room')->first();
    $response->assertRedirect(route('rooms.show', $room));
    $response->assertSessionHas('success', 'Room created successfully!');
});
test('room creation validates required fields', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->post(route('rooms.store'), []);

    $response->assertSessionHasErrors(['name', 'description', 'guest_count']);
});
test('room creation validates field lengths', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->post(route('rooms.store'), [
        'name' => str_repeat('A', 101), // Too long
        'description' => str_repeat('B', 501), // Too long
        'password' => str_repeat('P', 256), // Too long
        'guest_count' => 2
    ]);

    $response->assertSessionHasErrors(['name', 'description', 'password']);
});
test('room creation validates guest count range', function () {
    $user = User::factory()->create();

    // Test minimum (1 should be invalid)
    $response = actingAs($user)->post(route('rooms.store'), [
        'name' => 'Test Room',
        'description' => 'Test Description',
        'password' => 'password',
        'guest_count' => 1
    ]);
    $response->assertSessionHasErrors(['guest_count']);

    // Test maximum (7 should be invalid)
    $response = actingAs($user)->post(route('rooms.store'), [
        'name' => 'Test Room',
        'description' => 'Test Description',
        'password' => 'password',
        'guest_count' => 7
    ]);
    $response->assertSessionHasErrors(['guest_count']);
});
test('user can view room details', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['name' => 'Test Room']);
    RoomParticipant::factory()->count(2)->create(['room_id' => $room->id, 'left_at' => null]);

    $response = actingAs($user)->get(route('rooms.show', $room));

    $response->assertOk();
    $response->assertViewIs('rooms.show');
    $response->assertViewHas(['room', 'participants', 'user_is_creator', 'user_is_participant']);
    $response->assertSee('Test Room');
});
test('room show displays participant information', function () {
    $user = User::factory()->create(['username' => 'testuser']);
    $character = Character::factory()->create(['name' => 'Hero', 'class' => 'Warrior']);
    $room = Room::factory()->create();

    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => $character->id,
        'left_at' => null
    ]);

    $response = actingAs($user)->get(route('rooms.show', $room));

    $response->assertSee('Hero');
    $response->assertSee('Warrior');
    $response->assertSee('testuser');
});
test('user can view invite page with valid code', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['name' => 'Invite Room']);

    $response = actingAs($user)->get(route('rooms.invite', $room->invite_code));

    $response->assertOk();
    $response->assertViewIs('rooms.join');
    $response->assertViewHas(['room', 'characters']);
    $response->assertSee('Invite Room');
});
test('invite page shows 404 for invalid code', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->get(route('rooms.invite', 'INVALID1'));

    $response->assertNotFound();
});
test('invite page redirects if already participating', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create();
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'left_at' => null
    ]);

    $response = actingAs($user)->get(route('rooms.invite', $room->invite_code));

    $response->assertRedirect(route('rooms.show', $room));
    $response->assertSessionHas('info', 'You are already participating in this room.');
});
test('invite page redirects if room at capacity', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['guest_count' => 1]); // Total capacity = 2 (creator + 1 guest)

    // Fill the room to capacity (2 participants)
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'left_at' => null
    ]);
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'left_at' => null
    ]);

    $response = actingAs($user)->get(route('rooms.invite', $room->invite_code));

    $response->assertRedirect(route('rooms.index'));
    $response->assertSessionHas('error', 'This room is at capacity.');
});
test('user can join room with character', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user->id]);
    $room = Room::factory()->create();

    $response = actingAs($user)->post(route('rooms.join', $room), [
        'password' => 'password', // Factory default
        'character_id' => $character->id
    ]);

    $response->assertRedirect(route('rooms.session', $room));
    $response->assertSessionHas('success', 'Successfully joined the room!');

    assertDatabaseHas('room_participants', [
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => $character->id
    ]);
});
test('user can join room with temporary character', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create();

    $response = actingAs($user)->post(route('rooms.join', $room), [
        'password' => 'password',
        'character_name' => 'Temp Hero',
        'character_class' => 'Rogue'
    ]);

    $response->assertRedirect(route('rooms.session', $room));

    assertDatabaseHas('room_participants', [
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => null,
        'character_name' => 'Temp Hero',
        'character_class' => 'Rogue'
    ]);
});
test('user can join room without character', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create();

    $response = actingAs($user)->post(route('rooms.join', $room), [
        'password' => 'password',
        'character_name' => 'Test Character',
        'character_class' => 'Warrior'
    ]);

    $response->assertRedirect(route('rooms.session', $room));

    assertDatabaseHas('room_participants', [
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => null,
        'character_name' => 'Test Character',
        'character_class' => 'Warrior'
    ]);
});
test('joining room validates password', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create();

    $response = actingAs($user)->post(route('rooms.join', $room), [
        'password' => 'wrong_password'
    ]);

    $response->assertSessionHasErrors(['password' => 'Invalid room password.']);
    assertDatabaseMissing('room_participants', [
        'room_id' => $room->id,
        'user_id' => $user->id
    ]);
});
test('joining room validates character ownership', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherCharacter = Character::factory()->create(['user_id' => $otherUser->id]);
    $room = Room::factory()->create();

    $response = actingAs($user)->post(route('rooms.join', $room), [
        'password' => 'password',
        'character_id' => $otherCharacter->id
    ]);

    $response->assertNotFound();
    // ModelNotFoundException results in 404
});
test('user cannot join room twice', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create();

    // Join once
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'left_at' => null
    ]);

    $response = actingAs($user)->post(route('rooms.join', $room), [
        'password' => 'password'
    ]);

    $response->assertSessionHasErrors(['error' => 'You are already an active participant in this room.']);
});
test('user cannot join full room', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['guest_count' => 1]); // Total capacity = 2 (creator + 1 guest)

    // Fill the room to capacity (2 participants)
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'left_at' => null
    ]);
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'left_at' => null
    ]);

    $response = actingAs($user)->post(route('rooms.join', $room), [
        'password' => 'password'
    ]);

    $response->assertSessionHasErrors(['error' => 'This room is at capacity.']);
});
test('user can leave room they joined', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create();
    $participant = RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'left_at' => null
    ]);

    $response = actingAs($user)->delete(route('rooms.leave', $room));

    $response->assertRedirect(route('rooms.index'));
    $response->assertSessionHas('success', 'Successfully left the room.');

    $participant->refresh();
    expect($participant->left_at)->not->toBeNull();
});
test('user cannot leave room they havent joined', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create();

    $response = actingAs($user)->delete(route('rooms.leave', $room));

    $response->assertSessionHasErrors(['error' => 'You are not an active participant in this room.']);
});
test('user can access session if participating', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create();
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'left_at' => null
    ]);

    $response = actingAs($user)->get(route('rooms.session', $room));

    $response->assertOk();
    $response->assertViewIs('rooms.session');
    $response->assertViewHas(['room', 'participants']);
});
test('user cannot access session if not participating', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create();

    $response = actingAs($user)->get(route('rooms.session', $room));

    $response->assertRedirect(route('rooms.show', $room));
    $response->assertSessionHas('error', 'You must join the room first.');
});
test('room session includes javascript context', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create(['name' => 'JS Test Room']);
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'left_at' => null
    ]);

    $response = actingAs($user)->get(route('rooms.session', $room));

    $response->assertSee('window.roomData');
    $response->assertSee('window.currentUserId');
    $response->assertSee('JS Test Room');
    $response->assertSee('room-webrtc.js');
});
test('rooms with different guest counts show correctly', function () {
    $user = User::factory()->create();

    foreach ([1, 2, 3, 4, 5] as $guestCount) {
        $room = Room::factory()->create([
            'guest_count' => $guestCount,
            'name' => "Room {$guestCount}"
        ]);

        $response = actingAs($user)->get(route('rooms.show', $room));
        
        $response->assertSee("Max Guests: {$guestCount}");
    }
});
test('room invite url generation works', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create();

    $response = actingAs($user)->get(route('rooms.show', $room));

    $expectedInviteUrl = route('rooms.invite', ['invite_code' => $room->invite_code]);
    $response->assertSee($expectedInviteUrl);
});
test('room creator sees appropriate buttons', function () {
    $creator = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $creator->id]);

    $response = actingAs($creator)->get(route('rooms.show', $room));

    $response->assertSee('Start Session');
    $response->assertDontSee('Join Room');
});
test('room participant sees appropriate buttons', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create();
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'left_at' => null
    ]);

    $response = actingAs($user)->get(route('rooms.show', $room));

    $response->assertSee('Join Session');
    $response->assertSee('Leave Room');
    $response->assertDontSee('Join Room');
});
test('non participant sees join button', function () {
    $user = User::factory()->create();
    $room = Room::factory()->create();

    $response = actingAs($user)->get(route('rooms.show', $room));

    $response->assertSee('Join Room');
    $response->assertDontSee('Start Session');
    $response->assertDontSee('Join Session');
    $response->assertDontSee('Leave Room');
});
test('campaign creator can access campaign room creation form', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    $response = actingAs($user)->get(route('campaigns.rooms.create', $campaign->campaign_code));

    $response->assertOk();
    $response->assertSee('Create Campaign Room');
    $response->assertSee('No Password Required');
    $response->assertDontSee('Room Password');
});
test('campaign member can access campaign room creation form', function () {
    $creator = User::factory()->create();
    $member = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    CampaignMember::create([
        'campaign_id' => $campaign->id,
        'user_id' => $member->id,
    ]);

    $response = actingAs($member)->get(route('campaigns.rooms.create', $campaign->campaign_code));

    $response->assertOk();
    $response->assertSee('Create Campaign Room');
});
test('non campaign member cannot access campaign room creation', function () {
    $creator = User::factory()->create();
    $outsider = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    $response = actingAs($outsider)->get(route('campaigns.rooms.create', $campaign->campaign_code));

    $response->assertStatus(403);
});
test('campaign creator can create passwordless room', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    $roomData = [
        'name' => 'Test Campaign Room',
        'description' => 'A room for our campaign',
        'guest_count' => 4,
    ];

    $response = actingAs($user)->post(route('campaigns.rooms.store', $campaign->campaign_code), $roomData);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Campaign room created successfully!');

    assertDatabaseHas('rooms', [
        'name' => 'Test Campaign Room',
        'campaign_id' => $campaign->id,
        'creator_id' => $user->id,
        'password' => null, // No password for campaign rooms
    ]);
});
test('campaign member can create passwordless room', function () {
    $creator = User::factory()->create();
    $member = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    CampaignMember::create([
        'campaign_id' => $campaign->id,
        'user_id' => $member->id,
    ]);

    $roomData = [
        'name' => 'Member Campaign Room',
        'description' => 'Created by campaign member',
        'guest_count' => 3,
    ];

    $response = actingAs($member)->post(route('campaigns.rooms.store', $campaign->campaign_code), $roomData);

    $response->assertRedirect();
    assertDatabaseHas('rooms', [
        'name' => 'Member Campaign Room',
        'campaign_id' => $campaign->id,
        'creator_id' => $member->id,
        'password' => null,
    ]);
});
test('non campaign member cannot create campaign room', function () {
    $creator = User::factory()->create();
    $outsider = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    $roomData = [
        'name' => 'Unauthorized Room',
        'description' => 'Should not be created',
        'guest_count' => 2,
    ];

    $response = actingAs($outsider)->post(route('campaigns.rooms.store', $campaign->campaign_code), $roomData);

    $response->assertStatus(403);
    assertDatabaseMissing('rooms', [
        'name' => 'Unauthorized Room',
    ]);
});
test('campaign room restricts access to campaign members only', function () {
    $creator = User::factory()->create();
    $member = User::factory()->create();
    $outsider = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    CampaignMember::create([
        'campaign_id' => $campaign->id,
        'user_id' => $member->id,
    ]);

    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'campaign_id' => $campaign->id,
        'password' => null,
    ]);

    // Campaign creator can access
    $response = actingAs($creator)->get(route('rooms.show', $room));
    $response->assertOk();

    // Campaign member can access
    $response = actingAs($member)->get(route('rooms.show', $room));
    $response->assertOk();

    // Outsider cannot access
    $response = actingAs($outsider)->get(route('rooms.show', $room));
    $response->assertStatus(403);
});
test('campaign members can join passwordless campaign room', function () {
    $creator = User::factory()->create();
    $member = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    CampaignMember::create([
        'campaign_id' => $campaign->id,
        'user_id' => $member->id,
    ]);

    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'campaign_id' => $campaign->id,
        'password' => null,
    ]);

    // Member joins without password
    $response = actingAs($member)->post(route('rooms.join', $room), [
        'character_name' => 'Test Character',
        'character_class' => 'Warrior',
    ]);

    $response->assertRedirect(route('rooms.session', $room));
    assertDatabaseHas('room_participants', [
        'room_id' => $room->id,
        'user_id' => $member->id,
        'character_name' => 'Test Character',
    ]);
});
test('campaign room join form shows no password field', function () {
    $creator = User::factory()->create();
    $member = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $creator->id]);

    CampaignMember::create([
        'campaign_id' => $campaign->id,
        'user_id' => $member->id,
    ]);

    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'campaign_id' => $campaign->id,
        'password' => null,
    ]);

    $response = actingAs($member)->get(route('rooms.invite', $room->invite_code));

    $response->assertOk();
    $response->assertSee('No Password Required');
    $response->assertSee('campaign room - access is restricted to campaign members');
    $response->assertDontSee('Room Password');
    $response->assertDontSee('Enter room password');
});
test('user can create regular room without password', function () {
    $user = User::factory()->create();

    $roomData = [
        'name' => 'Open Room',
        'description' => 'Anyone can join',
        'guest_count' => 4,
        // No password provided
    ];

    $response = actingAs($user)->post(route('rooms.store'), $roomData);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Room created successfully!');

    assertDatabaseHas('rooms', [
        'name' => 'Open Room',
        'creator_id' => $user->id,
        'campaign_id' => null,
        'password' => null,
    ]);
});
test('user can create regular room with password', function () {
    $user = User::factory()->create();

    $roomData = [
        'name' => 'Protected Room',
        'description' => 'Password required',
        'password' => 'secret123',
        'guest_count' => 3,
    ];

    $response = actingAs($user)->post(route('rooms.store'), $roomData);

    $response->assertRedirect();

    $room = Room::where('name', 'Protected Room')->first();
    expect($room)->not->toBeNull();
    expect($room->password)->not->toBeNull();
    expect(Hash::check('secret123', $room->password))->toBeTrue();
});
test('regular room creation form shows optional password field', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->get(route('rooms.create'));

    $response->assertOk();
    $response->assertSee('Room Password');
    $response->assertSee('(Optional)');
    $response->assertSee('leave blank for no password');
});
test('user can join passwordless regular room', function () {
    $creator = User::factory()->create();
    $joiner = User::factory()->create();

    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'password' => null, // No password
        'campaign_id' => null, // Regular room
    ]);

    $response = actingAs($joiner)->post(route('rooms.join', $room), [
        'character_name' => 'Open Joiner',
        'character_class' => 'Rogue',
    ]);

    $response->assertRedirect(route('rooms.session', $room));
    assertDatabaseHas('room_participants', [
        'room_id' => $room->id,
        'user_id' => $joiner->id,
        'character_name' => 'Open Joiner',
    ]);
});
test('user can join password protected regular room with correct password', function () {
    $creator = User::factory()->create();
    $joiner = User::factory()->create();

    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'password' => Hash::make('secret123'),
        'campaign_id' => null,
    ]);

    $response = actingAs($joiner)->post(route('rooms.join', $room), [
        'password' => 'secret123',
        'character_name' => 'Protected Joiner',
        'character_class' => 'Wizard',
    ]);

    $response->assertRedirect(route('rooms.session', $room));
    assertDatabaseHas('room_participants', [
        'room_id' => $room->id,
        'user_id' => $joiner->id,
        'character_name' => 'Protected Joiner',
    ]);
});
test('user cannot join password protected room with wrong password', function () {
    $creator = User::factory()->create();
    $joiner = User::factory()->create();

    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'password' => Hash::make('secret123'),
        'campaign_id' => null,
    ]);

    $response = actingAs($joiner)->post(route('rooms.join', $room), [
        'password' => 'wrongpassword',
        'character_name' => 'Failed Joiner',
        'character_class' => 'Bard',
    ]);

    $response->assertSessionHasErrors(['password']);
    assertDatabaseMissing('room_participants', [
        'room_id' => $room->id,
        'user_id' => $joiner->id,
    ]);
});
test('user cannot join password protected room without password', function () {
    $creator = User::factory()->create();
    $joiner = User::factory()->create();

    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'password' => Hash::make('secret123'),
        'campaign_id' => null,
    ]);

    $response = actingAs($joiner)->post(route('rooms.join', $room), [
        // No password provided
        'character_name' => 'No Password Joiner',
        'character_class' => 'Druid',
    ]);

    $response->assertSessionHasErrors(['password']);
    assertDatabaseMissing('room_participants', [
        'room_id' => $room->id,
        'user_id' => $joiner->id,
    ]);
});
test('passwordless room join form shows no password field', function () {
    $creator = User::factory()->create();
    $joiner = User::factory()->create();

    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'password' => null,
        'campaign_id' => null,
    ]);

    $response = actingAs($joiner)->get(route('rooms.invite', $room->invite_code));

    $response->assertOk();
    $response->assertSee('No Password Required');
    $response->assertSee('This room is open to all participants');
    $response->assertDontSee('Room Password');
    $response->assertDontSee('Enter room password');
});
test('password protected room join form shows password field', function () {
    $creator = User::factory()->create();
    $joiner = User::factory()->create();

    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'password' => Hash::make('secret123'),
        'campaign_id' => null,
    ]);

    $response = actingAs($joiner)->get(route('rooms.invite', $room->invite_code));

    $response->assertOk();
    $response->assertSee('Room Password');
    $response->assertSee('Enter room password');
    $response->assertDontSee('No Password Required');
});
test('room sharing modal shows different messages for campaign vs regular rooms', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    // Campaign room
    $campaignRoom = Room::factory()->create([
        'creator_id' => $user->id,
        'campaign_id' => $campaign->id,
        'password' => null,
    ]);

    $response = actingAs($user)->get(route('rooms.show', $campaignRoom));
    $response->assertSee('Campaign members can join');

    // Regular room
    $regularRoom = Room::factory()->create([
        'creator_id' => $user->id,
        'campaign_id' => null,
        'password' => null,
    ]);

    $response = actingAs($user)->get(route('rooms.show', $regularRoom));
    $response->assertSee('Anyone with this link can join');
});

test('room creator can delete their room', function () {
    $creator = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $creator->id]);
    
    // Add some participants
    RoomParticipant::factory()->count(2)->create(['room_id' => $room->id]);
    
    $roomId = $room->id;
    
    $response = actingAs($creator)->delete(route('rooms.destroy', $room));
    
    $response->assertRedirect(route('rooms.index'));
    $response->assertSessionHas('success', 'Room deleted successfully.');
    
    // Room should be deleted
    assertDatabaseMissing('rooms', ['id' => $roomId]);
    
    // Participants should be deleted due to cascade
    assertDatabaseMissing('room_participants', ['room_id' => $roomId]);
});

test('non-creator cannot delete room', function () {
    $creator = User::factory()->create();
    $otherUser = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $creator->id]);
    
    $response = actingAs($otherUser)->delete(route('rooms.destroy', $room));
    
    $response->assertSessionHasErrors(['error']);
    
    // Room should still exist
    assertDatabaseHas('rooms', ['id' => $room->id]);
});

test('room creator cannot leave their own room', function () {
    $creator = User::factory()->create();
    $room = Room::factory()->create(['creator_id' => $creator->id]);
    
    // Manually add creator as participant (normally done by CreateRoomAction)
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $creator->id,
        'left_at' => null,
    ]);
    
    $response = actingAs($creator)->delete(route('rooms.leave', $room));
    
    $response->assertSessionHasErrors(['error']);
    
    // Room and participation should still exist
    assertDatabaseHas('rooms', ['id' => $room->id]);
    assertDatabaseHas('room_participants', [
        'room_id' => $room->id,
        'user_id' => $creator->id,
        'left_at' => null
    ]);
});
