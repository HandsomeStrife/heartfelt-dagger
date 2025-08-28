<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};
use Domain\Room\Models\RoomParticipant;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};
use Domain\User\Models\User;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};

beforeEach(function () {
    // Create users
    roomCreator = User::factory()->create([
        'username' => 'RoomCreator',
        'email' => 'creator@example.com',
    ]);
    
    participant = User::factory()->create([
        'username' => 'Participant',
        'email' => 'participant@example.com',
    ]);
    
    // Create a room (creator is automatically added as participant)
    room = Room::factory()->create([
        'name' => 'Test Room',
        'description' => 'A test room',
        'creator_id' => roomCreator->id,
        'guest_count' => 4, // Total capacity should be 5 (4 guests + 1 creator)
    ]);
    
    // The creator is automatically added as a participant by CreateRoomAction
    // Let's manually add to simulate this for testing
    RoomParticipant::factory()->create([
        'room_id' => room->id,
        'user_id' => roomCreator->id,
        'joined_at' => now(),
        'left_at' => null,
    ]);
    
    // Add another participant who joined the room
    RoomParticipant::factory()->create([
        'room_id' => room->id,
        'user_id' => participant->id,
        'character_name' => 'Test Character',
        'character_class' => 'Warrior',
        'joined_at' => now(),
        'left_at' => null,
    ]);
});

test('room creator does not see their own room in joined rooms', function () {
    actingAs(roomCreator);
    
    $response = get(route('rooms.index'));
    
    $response->assertOk();
    
    // Should see the room in "My Rooms" section
    $response->assertSee('My Rooms');
    $response->assertSee('Test Room');
    
    // Should NOT see the room in "Joined Rooms" section
    $response->assertSee('Joined Rooms');
    $response->assertSee('No joined rooms'); // Should show empty state
});

test('participant sees room in joined rooms but not created rooms', function () {
    actingAs(participant);
    
    $response = get(route('rooms.index'));
    
    $response->assertOk();
    
    // Should see empty "My Rooms" section
    $response->assertSee('My Rooms');
    $response->assertSee('No rooms yet');
    
    // Should see the room in "Joined Rooms" section  
    $response->assertSee('Joined Rooms');
    $response->assertSee('Test Room');
    $response->assertSee('Creator: RoomCreator');
});

test('participant counts are consistent across pages', function () {
    actingAs(roomCreator);
    
    // Check rooms index page
    $indexResponse = get(route('rooms.index'));
    $indexResponse->assertOk();
    $indexResponse->assertSee('2/5'); // 2 active participants out of 5 total capacity
    
    // Check room show page
    $showResponse = get(route('rooms.show', room));
    $showResponse->assertOk();
    $showResponse->assertSee('2 of 5 slots filled'); // Should match index page
    
    // Check room session page
    $sessionResponse = get(route('rooms.session', room));
    $sessionResponse->assertOk();
    $sessionResponse->assertSee('2/5 participants'); // Should match other pages
});

test('join page shows correct capacity', function () {
    actingAs(participant);
    
    $response = get(route('rooms.invite', room->invite_code));
    
    $response->assertOk();
    $response->assertSee('2/5 participants'); // Current participants / total capacity
});

test('room total capacity calculation is correct', function () {
    expect(room->guest_count)->toBe(4);
    expect(room->getTotalCapacity())->toBe(5); // guest_count + 1 (creator)
    expect(room->getActiveParticipantCount())->toBe(2); // creator + participant
});

test('room at capacity check works correctly', function () {
    // Room should not be at capacity (2/5)
    expect(room->isAtCapacity())->toBeFalse();
    
    // Add 3 more participants to reach capacity
    for ($i = 0; $i < 3; $i++) {
        $newUser = User::factory()->create();
        RoomParticipant::factory()->create([
            'room_id' => room->id,
            'user_id' => $newUser->id,
            'character_name' => "Character $i",
            'character_class' => 'Ranger',
            'joined_at' => now(),
            'left_at' => null,
        ]);
    }
    
    // Now should be at capacity (5/5)
    expect(room->fresh()->isAtCapacity())->toBeTrue();
    expect(room->fresh()->getActiveParticipantCount())->toBe(5);
});

test('room methods return consistent results', function () {
    // Refresh room to get latest data
    $room = room->fresh();
    
    // Test all the counting methods return consistent results
    expect($room->getActiveParticipantCount())->toBe(2);
    expect($room->getTotalCapacity())->toBe(5);
    expect($room->activeParticipants()->count())->toBe(2);
    expect($room->isAtCapacity())->toBeFalse();
});

test('rooms index displays use model methods not count attributes', function () {
    actingAs(roomCreator);
    
    $response = get(route('rooms.index'));
    
    // Verify the response contains the method calls, not just count attributes
    $response->assertOk();
    $content = $response->getContent();
    
    // Should use getActiveParticipantCount() and getTotalCapacity() methods
    expect($content)->toContain('2/5'); // This validates our fixes are working
});

test('left participants are not counted in active participant count', function () {
    // Participant leaves the room
    room->activeParticipants()
        ->where('user_id', participant->id)
        ->first()
        ->update(['left_at' => now()]);
    
    // Refresh and check counts
    $room = room->fresh();
    
    expect($room->getActiveParticipantCount())->toBe(1); // Only creator remains
    expect($room->activeParticipants()->count())->toBe(1);
    expect($room->isAtCapacity())->toBeFalse();
});

test('anonymous participants are counted correctly', function () {
    // Add an anonymous participant
    RoomParticipant::factory()->create([
        'room_id' => room->id,
        'user_id' => null, // Anonymous
        'character_name' => 'Anonymous Hero',
        'character_class' => 'Warrior',
        'joined_at' => now(),
        'left_at' => null,
    ]);
    
    // Refresh and check counts
    $room = room->fresh();
    
    expect($room->getActiveParticipantCount())->toBe(3); // creator + participant + anonymous
    expect($room->activeParticipants()->count())->toBe(3);
});
