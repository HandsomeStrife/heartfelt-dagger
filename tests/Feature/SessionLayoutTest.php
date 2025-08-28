<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};
use Domain\Room\Models\RoomParticipant;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};
use Domain\User\Models\User;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};

beforeEach(function () {
    roomCreator = User::factory()->create([
        'username' => 'RoomCreator',
        'email' => 'creator@example.com',
    ]);
});

test('session page shows correct number of slots for 5-capacity room', function () {
    // Create a room with 5 total capacity (4 guests + 1 creator)
    $room = Room::factory()->create([
        'name' => 'Test Room',
        'creator_id' => roomCreator->id,
        'guest_count' => 4, // Total capacity = 5
    ]);
    
    // Add creator as participant
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => roomCreator->id,
        'joined_at' => now(),
        'left_at' => null,
    ]);
    
    actingAs(roomCreator);
    
    $response = get(route('rooms.session', $room));
    
    $response->assertOk();
    
    // Should see 5 total slots:
    // - Top row: 3 slots
    // - Bottom row: 1 reserved slot + 2 actual slots (4, 5)
    
    // Check for empty slots being rendered
    $content = $response->getContent();
    
    // Should see multiple empty slots
    $emptySlotCount = substr_count($content, 'Empty Slot');
    expect($emptySlotCount)->toBeGreaterThanOrEqual(4); // At least 4 empty slots since only creator is present
    
    // Should see the "Reserved for the Void" decorative slot
    expect($content)->toContain('Reserved');
    expect($content)->toContain('For the Void');
});

test('session page shows correct number of slots for 4-capacity room', function () {
    // Create a room with 4 total capacity (3 guests + 1 creator)
    $room = Room::factory()->create([
        'name' => 'Test Room',
        'creator_id' => roomCreator->id,
        'guest_count' => 3, // Total capacity = 4
    ]);
    
    // Add creator as participant
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => roomCreator->id,
        'joined_at' => now(),
        'left_at' => null,
    ]);
    
    actingAs(roomCreator);
    
    $response = get(route('rooms.session', $room));
    
    $response->assertOk();
    
    // Should use 2x2 grid layout, not the 5+ layout
    $content = $response->getContent();
    
    // Should see 3 empty slots (since only creator is present in 4-slot room)
    $emptySlotCount = substr_count($content, 'Empty Slot');
    expect($emptySlotCount)->toBe(3);
    
    // Should NOT see the "Reserved for the Void" decorative slot (that's only for 5+ layout)
    expect($content)->not()->toContain('Reserved');
    expect($content)->not()->toContain('For the Void');
});

test('session page shows correct slots with multiple participants in 5-capacity room', function () {
    // Create a room with 5 total capacity
    $room = Room::factory()->create([
        'name' => 'Test Room',
        'creator_id' => roomCreator->id,
        'guest_count' => 4,
    ]);
    
    // Add creator as participant
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => roomCreator->id,
        'joined_at' => now(),
        'left_at' => null,
    ]);
    
    // Add 2 more participants
    for ($i = 0; $i < 2; $i++) {
        $user = User::factory()->create();
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'character_name' => "Character $i",
            'character_class' => 'Warrior',
            'joined_at' => now(),
            'left_at' => null,
        ]);
    }
    
    actingAs(roomCreator);
    
    $response = get(route('rooms.session', $room));
    
    $response->assertOk();
    
    $content = $response->getContent();
    
    // Should see 2 empty slots (5 total - 3 participants = 2 empty)
    $emptySlotCount = substr_count($content, 'Empty Slot');
    expect($emptySlotCount)->toBe(2);
    
    // Should still see the reserved slot
    expect($content)->toContain('Reserved');
    expect($content)->toContain('For the Void');
});

test('session page participant count matches total capacity', function () {
    $room = Room::factory()->create([
        'name' => 'Test Room',
        'creator_id' => roomCreator->id,
        'guest_count' => 4, // Total capacity = 5
    ]);
    
    // Add creator as participant
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => roomCreator->id,
        'joined_at' => now(),
        'left_at' => null,
    ]);
    
    actingAs(roomCreator);
    
    $response = get(route('rooms.session', $room));
    
    $response->assertOk();
    $response->assertSee('1/5 participants'); // 1 current participant out of 5 total capacity
});
