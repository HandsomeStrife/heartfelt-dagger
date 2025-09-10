<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;

test('debug apostrophe output', function () {
    // Create a user
    $user = User::factory()->create();
    
    // Create a character with apostrophes in the name
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => "O'Malley the Brave",
        'class' => 'ranger',
    ]);
    
    // Create a room
    $room = Room::factory()->create([
        'creator_id' => $user->id,
        'guest_count' => 1,
    ]);
    
    // Create a participant with the character
    RoomParticipant::factory()->create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'character_id' => $character->id,
    ]);
    
    // Visit the room session page
    $response = $this->actingAs($user)->get("/rooms/{$room->invite_code}/session");
    
    // Check that the response is successful
    $response->assertStatus(200);
    
    // Debug: Let's see what the actual content looks like
    $content = $response->getContent();
    
    // Extract character_name from the JavaScript
    if (preg_match('/character_name: (.+?)(?=,\s*character_class)/s', $content, $matches)) {
        echo "Found character_name: " . trim($matches[1]) . "\n";
    }
    
    expect(true)->toBeTrue(); // Just to make the test pass while we debug
});
