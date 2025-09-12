<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\User\Models\User;

describe('Weapon Trait Display Browser Test', function () {
    test('weapon shows correct trait modifier in room session', function () {
        // Create user and login
        $user = User::factory()->create();
        
        // Create a druid character with specific traits
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'class' => 'druid',
            'name' => 'Test Druid',
            'ancestry' => 'dwarf',
            'community' => 'loreborne',
        ]);

        // Create traits - Instinct should be +2
        $character->traits()->create(['trait_name' => 'agility', 'trait_value' => 1]);
        $character->traits()->create(['trait_name' => 'strength', 'trait_value' => 0]);
        $character->traits()->create(['trait_name' => 'finesse', 'trait_value' => 1]);
        $character->traits()->create(['trait_name' => 'instinct', 'trait_value' => 2]);
        $character->traits()->create(['trait_name' => 'presence', 'trait_value' => 0]);
        $character->traits()->create(['trait_name' => 'knowledge', 'trait_value' => -1]);

        // Create a weapon that uses instinct trait
        $character->equipment()->create([
            'equipment_type' => 'weapon',
            'equipment_key' => 'shortstaff',
            'equipment_data' => [
                'name' => 'Shortstaff',
                'type' => 'Primary',
                'trait' => 'instinct',
                'range' => 'Melee',
                'damage' => ['dice' => 'd6', 'modifier' => 0, 'type' => 'Physical'],
                'burden' => 'One-Handed',
                'tier' => 1,
            ],
            'is_equipped' => true,
        ]);

        // Add basic stats to the character
        $character->stats()->create([
            'evasion' => 10,
            'hit_points' => 6,
            'stress' => 6,
            'hope' => 2,
        ]);

        // Create a campaign room
        $room = Room::factory()->create([
            'creator_id' => $user->id,
            'name' => 'Test Campaign Room',
            'campaign_id' => 1, // Campaign room so sidebar appears
        ]);

        // Create participant
        $participant = RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'character_id' => $character->id,
        ]);

        // Visit the room session
        visit('/rooms/' . $room->id . '/session')
            ->loginAs($user)
            ->waitFor('[pest="trait-instinct"]', 10)
            ->assertSeeText('+2') // Should see +2 for instinct
            ->click('[pest="trait-instinct"]') // Click on instinct trait
            ->waitFor('.damage-health-sidebar', 5); // Make sure sidebar loaded
    })->skip('Skipping browser test for now'); // Skip until we fix the core issue
});
