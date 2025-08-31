<?php

declare(strict_types=1);

use Domain\Character\Data\CharacterData;
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};

test('character data handles null name gracefully', function () {
    $user = User::factory()->create();
    
    // Create a character with null name (simulating database state that caused the error)
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => null,
        'class' => null,
        'ancestry' => null,
        'community' => null,
    ]);

    // Should not throw an error and should use default values
    $characterData = CharacterData::fromModel($character);
    
    expect($characterData->name)->toBe('Unnamed Character');
    expect($characterData->class)->toBe('Warrior');
    expect($characterData->ancestry)->toBe('Human');
    expect($characterData->community)->toBe('Wanderborne');
});

test('character data preserves non-null values', function () {
    $user = User::factory()->create();
    
    // Create a character with proper values
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Character',
        'class' => 'Rogue',
        'ancestry' => 'Elf',
        'community' => 'Slyborne',
    ]);

    $characterData = CharacterData::fromModel($character);
    
    expect($characterData->name)->toBe('Test Character');
    expect($characterData->class)->toBe('Rogue');
    expect($characterData->ancestry)->toBe('Elf');
    expect($characterData->community)->toBe('Slyborne');
});

test('room join page loads with characters that have null fields', function () {
    $user = User::factory()->create();
    $room = \Domain\Room\Models\Room::factory()->passwordless()->create();
    
    // Create a character with null fields that previously caused errors
    Character::factory()->create([
        'user_id' => $user->id,
        'name' => null,
        'class' => null,
        'ancestry' => null,
        'community' => null,
    ]);

    // Should not error when loading the join page
    $response = actingAs($user)->get("/rooms/join/{$room->invite_code}");
    
    $response->assertOk();
    $response->assertSee('Join Room');
    $response->assertSee('Unnamed Character'); // Should display the default name
});
