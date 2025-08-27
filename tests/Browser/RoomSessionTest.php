<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;

test('room session has minimal layout without header and footer', function () {
    $creator = User::factory()->create([
        'username' => 'room_creator',
        'email' => 'creator@example.com',
        'password' => bcrypt('password'),
    ]);

    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Test Session Room',
        'guest_count' => 2, // Total capacity = 3
    ]);

    // Creator is automatically added as participant during room creation
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $creator->id,
        'left_at' => null,
    ]);

    $page = visit('/login');
    
    $page
        ->type('#email', $creator->email)
        ->type('#password', 'password')
        ->press('[data-testid="login-submit-button"]')
        ->wait(2000)
        ->assertPathIs('/dashboard')
        ->visit("/rooms/{$room->id}/session")
        ->wait(1000); // Wait for page to load

    // Verify header/navigation is NOT present
    $page->assertMissing('[data-testid="main-navigation"]')
        ->assertMissing('[data-testid="main-footer"]');

    // Verify session content IS present
    $page->assertPresent('[data-testid="room-info"]')
        ->assertPresent('[data-testid="room-name"]')
        ->assertPresent('[data-testid="participant-count"]')
        ->assertPresent('[data-testid="video-slot"]');

    $page->screenshot('room-session-minimal-layout');
});

test('room session creates correct number of video slots for different capacities', function () {
    $creator = User::factory()->create([
        'username' => 'test_creator',
        'email' => 'creator@test.com', 
        'password' => bcrypt('password'),
    ]);

    // Test different room capacities
    $testCases = [
        ['guest_count' => 2, 'total_capacity' => 3, 'expected_slots' => 3],
        ['guest_count' => 3, 'total_capacity' => 4, 'expected_slots' => 4], 
        ['guest_count' => 4, 'total_capacity' => 5, 'expected_slots' => 5],
        ['guest_count' => 5, 'total_capacity' => 6, 'expected_slots' => 6],
    ];

    foreach ($testCases as $case) {
        $room = Room::factory()->create([
            'creator_id' => $creator->id,
            'name' => "Test Room {$case['guest_count']} guests",
            'guest_count' => $case['guest_count'],
        ]);

        // Add creator as participant
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $creator->id,
            'left_at' => null,
        ]);

        $page = visit('/login');
        
        $page
            ->type('#email', $creator->email)
            ->type('#password', 'password')
            ->press('[data-testid="login-submit-button"]')
            ->waitForPath('/dashboard')
            ->visit("/rooms/{$room->id}/session")
            ->wait(1000);

        // Count video slots on the page
        $slots = $page->elements('[data-testid="video-slot"]');
        expect(count($slots))->toBe($case['expected_slots'], 
            "Room with {$case['guest_count']} guests should have {$case['expected_slots']} video slots");

        // Verify participant count display
        $page->assertPresent('[data-testid="participant-count"]');

        $page->screenshot("room-session-{$case['guest_count']}-guests");

        // Clean up for next iteration
        $room->delete();
    }
});

test('room session shows appropriate action buttons for creator vs participant', function () {
    $creator = User::factory()->create([
        'username' => 'room_creator',
        'email' => 'creator@example.com',
        'password' => bcrypt('password'),
    ]);

    $participant = User::factory()->create([
        'username' => 'participant',
        'email' => 'participant@example.com', 
        'password' => bcrypt('password'),
    ]);

    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Action Button Test Room',
        'guest_count' => 3,
    ]);

    // Add both users as participants
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

    // Test creator view - should see Delete Room button
    $page = visit('/login');
    
    $page
        ->type('#email', $creator->email)
        ->type('#password', 'password')
        ->press('[data-testid="login-submit-button"]')
        ->wait(2000)
        ->assertPathIs('/dashboard')
        ->visit("/rooms/{$room->id}/session")
        ->wait(1000)
        ->assertPresent('[data-testid="delete-room-button"]')
        ->assertMissing('[data-testid="leave-room-button"]');

    $page->screenshot('room-session-creator-buttons');

    // Test participant view - should see Leave Room button
    $page
        ->visit('/login')
        ->type('#email', $participant->email)
        ->type('#password', 'password')
        ->press('[data-testid="login-submit-button"]')
        ->wait(2000)
        ->assertPathIs('/dashboard')
        ->visit("/rooms/{$room->id}/session")
        ->wait(1000)
        ->assertPresent('[data-testid="leave-room-button"]')
        ->assertMissing('[data-testid="delete-room-button"]');

    $page->screenshot('room-session-participant-buttons');
});

test('room session includes webrtc javascript context', function () {
    $creator = User::factory()->create([
        'username' => 'webrtc_creator',
        'email' => 'webrtc@example.com',
        'password' => bcrypt('password'),
    ]);

    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'WebRTC Test Room',
        'guest_count' => 2,
    ]);

    // Add creator as participant
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $creator->id,
        'left_at' => null,
    ]);

    $page = visit('/login');
    
    $page
        ->type('#email', $creator->email)
        ->type('#password', 'password')
        ->press('[data-testid="login-submit-button"]')
        ->wait(2000)
        ->assertPathIs('/dashboard')
        ->visit("/rooms/{$room->id}/session")
        ->wait(1000);

    // Check for WebRTC JavaScript context
    $page->assertSeeIn('script', 'window.roomData')
        ->assertSeeIn('script', 'window.currentUserId')
        ->assertSeeIn('script', 'WebRTC Test Room')
        ->assertSeeIn('script', 'room-webrtc.js');

    $page->screenshot('room-session-webrtc-context');
});

test('room session shows different layouts for different participant counts', function () {
    $creator = User::factory()->create([
        'username' => 'layout_creator',
        'email' => 'layout@example.com',
        'password' => bcrypt('password'),
    ]);

    // Test 2 participants (side by side)
    $room2 = Room::factory()->create([
        'creator_id' => $creator->id,
        'name' => '2 Participant Room',
        'guest_count' => 2, // Total capacity = 3, but we'll test with 2 participants
    ]);

    RoomParticipant::factory()->create([
        'room_id' => $room2->id,
        'user_id' => $creator->id,
        'left_at' => null,
    ]);

    $page = visit('/login');
    
    $page
        ->type('#email', $creator->email)
        ->type('#password', 'password')
        ->press('[data-testid="login-submit-button"]')
        ->wait(2000)
        ->assertPathIs('/dashboard')
        ->visit("/rooms/{$room2->id}/session")
        ->wait(1000);

    // Check for side-by-side layout (grid-cols-2)
    $page->assertPresent('.grid-cols-2');
    
    $page->screenshot('room-session-layout-2-participants');

    // Test 3 participants (triangle layout)
    $room3 = Room::factory()->create([
        'creator_id' => $creator->id,
        'name' => '3 Participant Room',
        'guest_count' => 3, // Total capacity = 4
    ]);

    RoomParticipant::factory()->create([
        'room_id' => $room3->id,
        'user_id' => $creator->id,
        'left_at' => null,
    ]);

    $page
        ->visit('/login')
        ->type('#email', $creator->email)
        ->type('#password', 'password')
        ->press('[data-testid="login-submit-button"]')
        ->wait(2000)
        ->assertPathIs('/dashboard')
        ->visit("/rooms/{$room3->id}/session")
        ->wait(1000);

    // Check for triangle layout elements
    $page->assertSee('Triangle layout') // Comment in template
        ->assertPresent('.h-1/2'); // Top and bottom sections

    $page->screenshot('room-session-layout-3-participants');

    // Test 4 participants (2x2 grid)
    $room4 = Room::factory()->create([
        'creator_id' => $creator->id,
        'name' => '4 Participant Room',
        'guest_count' => 4, // Total capacity = 5
    ]);

    RoomParticipant::factory()->create([
        'room_id' => $room4->id,
        'user_id' => $creator->id,
        'left_at' => null,
    ]);

    $page
        ->visit('/login')
        ->type('#email', $creator->email)
        ->type('#password', 'password')
        ->press('[data-testid="login-submit-button"]')
        ->wait(2000)
        ->assertPathIs('/dashboard')
        ->visit("/rooms/{$room4->id}/session")
        ->wait(1000);

    // Check for 2x2 grid layout
    $page->assertPresent('.grid-cols-2.grid-rows-2');

    $page->screenshot('room-session-layout-4-participants');

    // Test 5+ participants (2x3 grid with "Reserved for the Void")
    $room5 = Room::factory()->create([
        'creator_id' => $creator->id,
        'name' => '5+ Participant Room',
        'guest_count' => 5, // Total capacity = 6
    ]);

    RoomParticipant::factory()->create([
        'room_id' => $room5->id,
        'user_id' => $creator->id,
        'left_at' => null,
    ]);

    $page
        ->visit('/login')
        ->type('#email', $creator->email)
        ->type('#password', 'password')
        ->press('[data-testid="login-submit-button"]')
        ->wait(2000)
        ->assertPathIs('/dashboard')
        ->visit("/rooms/{$room5->id}/session")
        ->wait(1000);

    // Check for 2x3 grid and special "Reserved for the Void" slot
    $page->assertPresent('.grid-cols-3.grid-rows-2')
        ->assertSee('Reserved')
        ->assertSee('For the Void');

    $page->screenshot('room-session-layout-5-participants');
});
