<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;

beforeEach(function () {
    $this->roomCreator = User::factory()->create([
        'username' => 'RoomCreator',
        'email' => 'creator@example.com',
    ]);
});

test('session page shows correct number of slots for 5-capacity room', function () {
    // Create a room with 5 total capacity (4 guests + 1 creator)
    $room = Room::factory()->create([
        'name' => 'Test Room',
        'creator_id' => $this->roomCreator->id,
        'guest_count' => 4, // Total capacity = 5
    ]);
    
    // Add creator as participant
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $this->roomCreator->id,
        'joined_at' => now(),
        'left_at' => null,
    ]);
    
    actingAs($this->roomCreator);
    
    $response = get(route('rooms.session', $room));
    
    $response->assertOk();
    
    // Should see 5 total slots:
    // - Top row: 3 slots
    // - Bottom row: 1 reserved slot + 2 actual slots (4, 5)
    
    // Check for empty slots being rendered
    $content = $response->getContent();
    
    // Should see multiple waiting for participant slots
    $waitingSlotCount = substr_count($content, 'Waiting for participant');
    expect($waitingSlotCount)->toBeGreaterThanOrEqual(4); // At least 4 empty slots since only creator is present
    
    // Should see the "Reserved" GM slot
    expect($content)->toContain('Reserved');
    expect($content)->toContain('GM Slot');
});

test('session page shows correct number of slots for 4-capacity room', function () {
    // Create a room with 4 total capacity (3 guests + 1 creator)
    $room = Room::factory()->create([
        'name' => 'Test Room',
        'creator_id' => $this->roomCreator->id,
        'guest_count' => 3, // Total capacity = 4
    ]);
    
    // Add creator as participant
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $this->roomCreator->id,
        'joined_at' => now(),
        'left_at' => null,
    ]);
    
    actingAs($this->roomCreator);
    
    $response = get(route('rooms.session', $room));
    
    $response->assertOk();
    
    // Should use 2x2 grid layout, not the 5+ layout
    $content = $response->getContent();
    
    // Should see 3 waiting slots (since only creator is present in 4-slot room)
    $waitingSlotCount = substr_count($content, 'Waiting for participant');
    expect($waitingSlotCount)->toBe(3);
    
    // Should see the "Reserved" GM slot for slot 1
    expect($content)->toContain('Reserved');
    expect($content)->toContain('GM Slot');
});

test('session page shows correct slots with multiple participants in 5-capacity room', function () {
    // Create a room with 5 total capacity
    $room = Room::factory()->create([
        'name' => 'Test Room',
        'creator_id' => $this->roomCreator->id,
        'guest_count' => 4,
    ]);
    
    // Add creator as participant
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $this->roomCreator->id,
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
    
    actingAs($this->roomCreator);
    
    $response = get(route('rooms.session', $room));
    
    $response->assertOk();
    
    $content = $response->getContent();
    
    // Should see 2 waiting slots (5 total - 3 participants = 2 empty)
    $waitingSlotCount = substr_count($content, 'Waiting for participant');
    expect($waitingSlotCount)->toBe(2);
    
    // Should still see the reserved slot
    expect($content)->toContain('Reserved');
    expect($content)->toContain('GM Slot');
});

test('session page participant count matches total capacity', function () {
    $room = Room::factory()->create([
        'name' => 'Test Room',
        'creator_id' => $this->roomCreator->id,
        'guest_count' => 4, // Total capacity = 5
    ]);
    
    // Add creator as participant
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $this->roomCreator->id,
        'joined_at' => now(),
        'left_at' => null,
    ]);
    
    actingAs($this->roomCreator);
    
    $response = get(route('rooms.session', $room));
    
    $response->assertOk();
    $response->assertSee('1/5 participants'); // 1 current participant out of 5 total capacity
});

// Additional comprehensive layout tests

test('single participant layout renders correctly', function () {
    $room = Room::factory()->create([
        'name' => 'Solo Room',
        'creator_id' => $this->roomCreator->id,
        'guest_count' => 0, // Total capacity = 1 (just creator)
    ]);
    
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $this->roomCreator->id,
        'joined_at' => now(),
        'left_at' => null,
    ]);
    
    actingAs($this->roomCreator);
    
    $response = get(route('rooms.session', $room));
    
    $response->assertOk();
    $content = $response->getContent();
    
    // Should use single layout component
    expect($content)->toContain('h-full w-full'); // Single layout wrapper
    expect($content)->not()->toContain('grid-cols-2'); // No grid layout
    expect($content)->not()->toContain('Reserved'); // No reserved slot
    
    // Should show 1 participant slot
    $emptySlotCount = substr_count($content, 'Empty Slot');
    expect($emptySlotCount)->toBe(0); // No empty slots for single occupied room
});

test('dual participant layout renders correctly', function () {
    $room = Room::factory()->create([
        'name' => 'Duo Room',
        'creator_id' => $this->roomCreator->id,
        'guest_count' => 1, // Total capacity = 2
    ]);
    
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $this->roomCreator->id,
        'joined_at' => now(),
        'left_at' => null,
    ]);
    
    actingAs($this->roomCreator);
    
    $response = get(route('rooms.session', $room));
    
    $response->assertOk();
    $content = $response->getContent();
    
    // Should use dual layout component with side-by-side grid
    expect($content)->toContain('grid-cols-2'); // Dual layout grid
    expect($content)->not()->toContain('grid-cols-3'); // Not grid layout
    expect($content)->not()->toContain('Reserved'); // No reserved slot
    
    // Should show 1 empty slot (2 total - 1 participant)
    $emptySlotCount = substr_count($content, 'Empty Slot');
    expect($emptySlotCount)->toBe(1);
});

test('triangle layout renders correctly', function () {
    $room = Room::factory()->create([
        'name' => 'Triangle Room',
        'creator_id' => $this->roomCreator->id,
        'guest_count' => 2, // Total capacity = 3
    ]);
    
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $this->roomCreator->id,
        'joined_at' => now(),
        'left_at' => null,
    ]);
    
    actingAs($this->roomCreator);
    
    $response = get(route('rooms.session', $room));
    
    $response->assertOk();
    $content = $response->getContent();
    
    // Should use triangle layout with specific structure
    expect($content)->toContain('h-1/2'); // Triangle layout rows
    expect($content)->toContain('w-1/2'); // Top centered element
    expect($content)->not()->toContain('grid-cols-3'); // Not grid layout
    expect($content)->not()->toContain('Reserved'); // No reserved slot
    
    // Should show 2 empty slots (3 total - 1 participant)
    $emptySlotCount = substr_count($content, 'Empty Slot');
    expect($emptySlotCount)->toBe(2);
});

test('quad layout renders correctly', function () {
    $room = Room::factory()->create([
        'name' => 'Quad Room',
        'creator_id' => $this->roomCreator->id,
        'guest_count' => 3, // Total capacity = 4
    ]);
    
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $this->roomCreator->id,
        'joined_at' => now(),
        'left_at' => null,
    ]);
    
    actingAs($this->roomCreator);
    
    $response = get(route('rooms.session', $room));
    
    $response->assertOk();
    $content = $response->getContent();
    
    // Should use quad layout with 2x2 grid
    expect($content)->toContain('grid-cols-2 grid-rows-2'); // Quad layout grid
    expect($content)->not()->toContain('grid-cols-3'); // Not 6-slot grid layout
    expect($content)->not()->toContain('Reserved'); // No reserved slot
    
    // Should show 3 empty slots (4 total - 1 participant)
    $emptySlotCount = substr_count($content, 'Empty Slot');
    expect($emptySlotCount)->toBe(3);
});

test('grid layout renders correctly with 6 participants', function () {
    $room = Room::factory()->create([
        'name' => 'Large Room',
        'creator_id' => $this->roomCreator->id,
        'guest_count' => 5, // Total capacity = 6
    ]);
    
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $this->roomCreator->id,
        'joined_at' => now(),
        'left_at' => null,
    ]);
    
    actingAs($this->roomCreator);
    
    $response = get(route('rooms.session', $room));
    
    $response->assertOk();
    $content = $response->getContent();
    
    // Should use grid layout with 2x3 setup
    expect($content)->toContain('grid-cols-3 grid-rows-2'); // Grid layout
    expect($content)->toContain('Reserved'); // Reserved slot present
    expect($content)->toContain('For the Void'); // Reserved slot text
    
    // Should show 4 empty slots (6 total - 1 participant - 1 reserved)
    $emptySlotCount = substr_count($content, 'Empty Slot');
    expect($emptySlotCount)->toBe(4);
});

test('layout components receive correct props', function () {
    $room = Room::factory()->create([
        'name' => 'Props Test Room',
        'creator_id' => $this->roomCreator->id,
        'guest_count' => 3, // Total capacity = 4 (quad layout)
    ]);
    
    // Add creator and one additional participant
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $this->roomCreator->id,
        'joined_at' => now(),
        'left_at' => null,
    ]);
    
    $additionalUser = User::factory()->create();
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $additionalUser->id,
        'character_name' => 'Test Warrior',
        'character_class' => 'Warrior',
        'joined_at' => now(),
        'left_at' => null,
    ]);
    
    actingAs($this->roomCreator);
    
    $response = get(route('rooms.session', $room));
    
    $response->assertOk();
    $content = $response->getContent();
    
    // Should show both participants
    expect($content)->toContain('RoomCreator'); // Creator username
    expect($content)->toContain('Test Warrior'); // Participant character name
    expect($content)->toContain('Warrior'); // Character class
    
    // Should show 2 empty slots (4 total - 2 participants)
    $emptySlotCount = substr_count($content, 'Empty Slot');
    expect($emptySlotCount)->toBe(2);
});

test('layout correctly handles host identification', function () {
    $room = Room::factory()->create([
        'name' => 'Host Test Room',
        'creator_id' => $this->roomCreator->id,
        'guest_count' => 2, // Total capacity = 3 (triangle layout)
    ]);
    
    // Add creator as participant
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $this->roomCreator->id,
        'joined_at' => now(),
        'left_at' => null,
    ]);
    
    // Add regular participant
    $regularUser = User::factory()->create();
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $regularUser->id,
        'character_name' => 'Regular Player',
        'character_class' => 'Ranger',
        'joined_at' => now(),
        'left_at' => null,
    ]);
    
    actingAs($this->roomCreator);
    
    $response = get(route('rooms.session', $room));
    
    $response->assertOk();
    $content = $response->getContent();
    
    // Creator should be marked as host in first slot (triangle layout)
    expect($content)->toContain('(Host)'); // Host indicator
    expect($content)->toContain('RoomCreator'); // Creator name
    expect($content)->toContain('Regular Player'); // Participant name
    
    // Should show 1 empty slot (3 total - 2 participants)
    $emptySlotCount = substr_count($content, 'Empty Slot');
    expect($emptySlotCount)->toBe(1);
});

test('layout handles empty slots correctly across all layouts', function () {
    $testCases = [
        ['guest_count' => 0, 'expected_empty' => 0], // Single layout (1 total, 1 participant)
        ['guest_count' => 1, 'expected_empty' => 1], // Dual layout (2 total, 1 participant)
        ['guest_count' => 2, 'expected_empty' => 2], // Triangle layout (3 total, 1 participant)
        ['guest_count' => 3, 'expected_empty' => 3], // Quad layout (4 total, 1 participant)
        ['guest_count' => 4, 'expected_empty' => 4], // Grid layout (5 total, 1 participant)
        ['guest_count' => 5, 'expected_empty' => 4], // Grid layout (6 total, 1 participant, 1 reserved)
    ];
    
    foreach ($testCases as $case) {
        $room = Room::factory()->create([
            'name' => "Test Room {$case['guest_count']}",
            'creator_id' => $this->roomCreator->id,
            'guest_count' => $case['guest_count'],
        ]);
        
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $this->roomCreator->id,
            'joined_at' => now(),
            'left_at' => null,
        ]);
        
        actingAs($this->roomCreator);
        
        $response = get(route('rooms.session', $room));
        $content = $response->getContent();
        
        $emptySlotCount = substr_count($content, 'Empty Slot');
        expect($emptySlotCount)->toBe($case['expected_empty'], 
            "Guest count {$case['guest_count']} should have {$case['expected_empty']} empty slots but found {$emptySlotCount}"
        );
    }
});
