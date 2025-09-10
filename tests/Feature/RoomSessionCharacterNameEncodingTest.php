<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;

describe('Room session character name encoding', function () {
    test('character names with quotes are properly encoded in JavaScript', function () {
        // Create a user
        $user = User::factory()->create();
        
        // Create a character with quotes in the name
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'name' => 'Sir "Quotey" McQuoteface',
            'class' => 'warrior',
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
        
        // Check that the character name is properly JSON encoded in the JavaScript
        $response->assertSee('character_name: "Sir \\u0022Quotey\\u0022 McQuoteface"', false);
        
        // Check that the name appears correctly in the HTML (displayed with quotes)
        $response->assertSee('Sir "Quotey" McQuoteface');
        
        // Verify the JavaScript doesn't contain unescaped quotes that would break it
        $response->assertDontSee('character_name: "Sir "Quotey" McQuoteface"', false);
    });
    
    test('character names with apostrophes are properly encoded', function () {
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
        
        // Check that the character name is properly JSON encoded
        $response->assertSee('character_name: "O\\u0027Malley the Brave"', false);
        
        // Check that the name appears correctly in the HTML
        $response->assertSee("O'Malley the Brave");
    });
    
    test('room names with quotes are properly encoded in JavaScript', function () {
        // Create a user
        $user = User::factory()->create();
        
        // Create a room with quotes in the name
        $room = Room::factory()->create([
            'creator_id' => $user->id,
            'guest_count' => 1,
            'name' => 'The "Adventure" Begins',
        ]);
        
        // Create a participant
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
        ]);
        
        // Visit the room session page
        $response = $this->actingAs($user)->get("/rooms/{$room->invite_code}/session");
        
        // Check that the response is successful
        $response->assertStatus(200);
        
        // Check that the room name is properly JSON encoded
        $response->assertSee('name: "The \\u0022Adventure\\u0022 Begins"', false);
        
        // Check that the name appears correctly in the HTML
        $response->assertSee('The "Adventure" Begins');
        
        // Verify the JavaScript doesn't contain unescaped quotes
        $response->assertDontSee('name: "The "Adventure" Begins"', false);
    });
    
    test('participant character_name field uses fallback correctly with quotes', function () {
        // Create a user
        $user = User::factory()->create([
            'username' => 'player_with_quotes',
        ]);
        
        // Create a room
        $room = Room::factory()->create([
            'creator_id' => $user->id,
            'guest_count' => 1,
        ]);
        
        // Create a participant with character_name field (no character model) containing quotes
        RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'character_id' => null,
            'character_name' => 'Flex "The Quick" Johnson',
            'character_class' => 'rogue',
        ]);
        
        // Visit the room session page
        $response = $this->actingAs($user)->get("/rooms/{$room->invite_code}/session");
        
        // Check that the response is successful
        $response->assertStatus(200);
        
        // Check that the fallback character_name is properly JSON encoded
        $response->assertSee('character_name: "Flex \\u0022The Quick\\u0022 Johnson"', false);
        
        // Verify no broken JavaScript
        $response->assertDontSee('character_name: "Flex "The Quick" Johnson"', false);
    });
});
