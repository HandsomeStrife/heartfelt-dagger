<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};

test('room session page uses minimal layout without header and footer', function () {
    $creator = User::factory()->create();
    $room = Room::factory()->passwordless()->create(['creator_id' => $creator->id]);
    
    // Add creator as participant
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $creator->id,
        'left_at' => null,
    ]);

    $response = actingAs($creator)->get("/rooms/{$room->invite_code}/session");

    $response->assertOk();
    
    // Verify minimal layout is used (no navigation or footer data-testids)
    $response->assertDontSee('data-testid="main-navigation"', false);
    $response->assertDontSee('data-testid="main-footer"', false);
    
    // Verify session elements are present
    $response->assertSee('data-testid="room-info"', false);
    $response->assertSee('data-testid="room-name"', false);
    $response->assertSee('data-testid="participant-count"', false);
    $response->assertSee('data-testid="video-slot"', false);
});

test('room session creates video slots that scale with capacity', function () {
    $creator = User::factory()->create();
    
    $testCases = [
        ['guest_count' => 2], // getTotalCapacity() = 3
        ['guest_count' => 3], // getTotalCapacity() = 4  
        ['guest_count' => 4], // getTotalCapacity() = 5
        ['guest_count' => 5], // getTotalCapacity() = 6
        ['guest_count' => 6], // getTotalCapacity() = 7 (max)
    ];

    foreach ($testCases as $case) {
        $room = Room::factory()->passwordless()->create([
        'creator_id' => $creator->id,
        'guest_count' => $case['guest_count'],
    ]);

        // Add creator as participant
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $creator->id,
            'left_at' => null,
        ]);

        $response = actingAs($creator)->get("/rooms/{$room->invite_code}/session");
        
        // Verify video slots are present and scale appropriately
        $content = $response->getContent();
        $videoSlotCount = substr_count($content, 'data-testid="video-slot"');
        
        // Should have at least 2 slots and at most 6 slots for valid room sizes
        expect($videoSlotCount)->toBeGreaterThanOrEqual(2, 
            "Room with {$case['guest_count']} guests should have at least 2 video slots");
        expect($videoSlotCount)->toBeLessThanOrEqual(6, 
            "Room with {$case['guest_count']} guests should have at most 6 video slots");
    }
});

test('room session shows appropriate buttons for creator vs participant', function () {
    $creator = User::factory()->create();
    $participant = User::factory()->create();
    
    $room = Room::factory()->passwordless()->create(['creator_id' => $creator->id]);
    
    // Add both as participants
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $creator->id,
        'left_at' => null,
    ]);
    
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $participant->id,
        'left_at' => null,
    ]);

    // Creator should see delete button
    $creatorResponse = actingAs($creator)->get("/rooms/{$room->invite_code}/session");
    $creatorResponse->assertSee('data-testid="delete-room-button"', false);
    $creatorResponse->assertDontSee('data-testid="leave-room-button"', false);

    // Participant should see leave button
    $participantResponse = actingAs($participant)->get("/rooms/{$room->invite_code}/session");
    $participantResponse->assertSee('data-testid="leave-room-button"', false);
    $participantResponse->assertDontSee('data-testid="delete-room-button"', false);
});

test('room session includes required webrtc javascript context', function () {
    $creator = User::factory()->create();
    $room = Room::factory()->passwordless()->create([
        'creator_id' => $creator->id,
        'name' => 'WebRTC Test Room',
    ]);
    
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $creator->id,
        'left_at' => null,
    ]);

    $response = actingAs($creator)->get("/rooms/{$room->invite_code}/session");

    // Verify WebRTC JavaScript context is present
    $response->assertSee('window.roomData');
    $response->assertSee('window.currentUserId');
    $response->assertSee('WebRTC Test Room');
    $response->assertSee('room-webrtc.js');
});

test('room session layout elements are correct for different capacities', function () {
    $creator = User::factory()->create();

    // Test 2-participant layout (side by side)
    $room2 = Room::factory()->passwordless()->create([
        'creator_id' => $creator->id,
        'guest_count' => 2, // Total capacity = 3
    ]);

    RoomParticipant::factory()->create([
        'room_id' => $room2->id,
        'user_id' => $creator->id,
        'left_at' => null,
    ]);

    $response2 = actingAs($creator)->get("/rooms/{$room2->invite_code}/session");
    $response2->assertSee('grid-cols-2'); // Side by side layout

    // Test 5+ participant layout (2x3 grid with void slot)
    $room5 = Room::factory()->passwordless()->create([
        'creator_id' => $creator->id,
        'guest_count' => 5, // Total capacity = 6
    ]);

    RoomParticipant::factory()->create([
        'room_id' => $room5->id,
        'user_id' => $creator->id,
        'left_at' => null,
    ]);

    $response5 = actingAs($creator)->get("/rooms/{$room5->invite_code}/session");
    $response5->assertSee('grid-cols-3 grid-rows-2'); // 2x3 grid
    $response5->assertSee('Reserved'); // Special void slot
    $response5->assertSee('For the Void');
});

test('room overview page shows delete button for creator', function () {
    $creator = User::factory()->create();
    $participant = User::factory()->create();
    
    $room = Room::factory()->passwordless()->create(['creator_id' => $creator->id]);
    
    // Add both as participants
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $creator->id,
        'left_at' => null,
    ]);
    
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $participant->id,
        'left_at' => null,
    ]);

    // Creator should see delete button on overview page
    $creatorResponse = actingAs($creator)->get("/rooms/{$room->invite_code}");
    $creatorResponse->assertOk();
    $creatorResponse->assertSee('data-testid="delete-room-button"', false);
    $creatorResponse->assertSee('Delete Room');
    $creatorResponse->assertSee('Start Session');

    // Participant should NOT see delete button, only leave button
    $participantResponse = actingAs($participant)->get("/rooms/{$room->invite_code}");
    $participantResponse->assertOk();
    $participantResponse->assertDontSee('data-testid="delete-room-button"', false);
    $participantResponse->assertSee('Leave Room');
    $participantResponse->assertSee('Join Session');
});
