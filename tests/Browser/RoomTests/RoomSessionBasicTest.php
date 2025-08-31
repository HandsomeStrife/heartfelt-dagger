<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;

test('room session page structure is correct', function () {
    // Create test data
    $creator = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $room = Room::factory()->create([
        'creator_id' => $creator->id,
        'name' => 'Browser Test Room',
        'guest_count' => 3, // Total capacity = 4
    ]);

    // Add creator as participant
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $creator->id,
        'left_at' => null,
    ]);

    // Simple login and session visit
    $page = visit('/login')
        ->type('#email', 'test@example.com')
        ->type('#password', 'password')
        ->press('[data-testid="login-submit-button"]')
        ->wait(5000) // Give enough time for login
        ->visit("/rooms/{$room->id}/session")
        ->wait(3000); // Give time for session to load

    // Core verification: Header and footer should be missing (minimal layout)
    $page->assertMissing('[data-testid="main-navigation"]');
    $page->assertMissing('[data-testid="main-footer"]');

    // Core verification: Session elements should be present
    $page->assertPresent('[data-testid="room-info"]');
    $page->assertPresent('[data-testid="video-slot"]');

    // Count video slots for capacity verification
    $videoSlots = $page->elements('[data-testid="video-slot"]');
    $slotCount = count($videoSlots);
    
    // Should have 4 slots for guest_count=3 (3 guests + 1 creator)
    expect($slotCount)->toBeGreaterThanOrEqual(3, 'Should have at least 3 video slots');
    expect($slotCount)->toBeLessThanOrEqual(6, 'Should have at most 6 video slots');

    // Verify creator sees delete button (not leave button)
    $page->assertPresent('[data-testid="delete-room-button"]');
    $page->assertMissing('[data-testid="leave-room-button"]');

    $page->screenshot('room-session-structure-test');
});
