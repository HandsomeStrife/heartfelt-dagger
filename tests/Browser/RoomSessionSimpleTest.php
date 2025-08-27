<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;

test('room session layout and video slots are correct', function () {
    // Create user and room data directly
    $creator = User::factory()->create([
        'username' => 'test_creator',
        'email' => 'creator@example.com',
        'password' => bcrypt('password'),
    ]);

    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Test Room',
        'guest_count' => 3, // Total capacity = 4
    ]);

    // Add creator as participant
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $creator->id,
        'left_at' => null,
    ]);

    // Directly authenticate the user and visit the session
    $page = visit('/login');
    
    $page
        ->type('#email', $creator->email)
        ->type('#password', 'password')
        ->press('[data-testid="login-submit-button"]')
        ->wait(3000) // Wait for login to complete
        ->visit("/rooms/{$room->id}/session")
        ->wait(2000); // Wait for session to load

    // Test 1: Verify minimal layout (no header/footer)
    $page->assertMissing('[data-testid="main-navigation"]')
        ->assertMissing('[data-testid="main-footer"]');

    // Test 2: Verify session content is present
    $page->assertPresent('[data-testid="room-info"]')
        ->assertPresent('[data-testid="room-name"]')
        ->assertPresent('[data-testid="participant-count"]');

    // Test 3: Count video slots (should be 4 for guest_count=3)
    $slots = $page->elements('[data-testid="video-slot"]');
    expect(count($slots))->toBe(4, "Room with 3 guests should have 4 total video slots");

    // Test 4: Verify room creator sees delete button
    $page->assertPresent('[data-testid="delete-room-button"]')
        ->assertMissing('[data-testid="leave-room-button"]');

    $page->screenshot('room-session-complete-test');
});

test('room session video slots scale with capacity', function () {
    $creator = User::factory()->create([
        'email' => 'scaling@example.com',
        'password' => bcrypt('password'),
    ]);

    // Test different capacities
    $testCases = [
        ['guest_count' => 2, 'expected_slots' => 3],
        ['guest_count' => 5, 'expected_slots' => 6],
    ];

    foreach ($testCases as $case) {
        $room = Room::factory()->create([
            'creator_id' => $creator->id,
            'guest_count' => $case['guest_count'],
        ]);

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
            ->wait(3000)
            ->visit("/rooms/{$room->id}/session")
            ->wait(2000);

        // Count video slots
        $slots = $page->elements('[data-testid="video-slot"]');
        expect(count($slots))->toBe($case['expected_slots'], 
            "Room with {$case['guest_count']} guests should have {$case['expected_slots']} slots");

        $page->screenshot("room-session-{$case['guest_count']}-guests");
        
        // Clean up
        $room->delete();
    }
});

test('room session shows correct layout elements', function () {
    $creator = User::factory()->create([
        'email' => 'layout@example.com',
        'password' => bcrypt('password'),
    ]);

    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Layout Test Room',
        'guest_count' => 4, // Total capacity = 5
    ]);

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
        ->wait(3000)
        ->visit("/rooms/{$room->id}/session")
        ->wait(2000);

    // Verify specific layout elements for 5-slot room
    $page->assertPresent('.grid-cols-3.grid-rows-2') // 2x3 grid
        ->assertSee('Reserved') // Special void slot
        ->assertSee('For the Void');

    // Verify WebRTC context is loaded
    $page->assertSeeIn('script', 'window.roomData')
        ->assertSeeIn('script', 'window.currentUserId')
        ->assertSeeIn('script', 'Layout Test Room');

    $page->screenshot('room-session-layout-elements');
});
