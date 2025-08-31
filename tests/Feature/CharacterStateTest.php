<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Livewire\Livewire;

test('authenticated user can save character state to database', function () {
    // Create a user and character
    $user = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user->id]);
    
    // Act as the authenticated user
    $this->actingAs($user);
    
    // Create the character viewer component
    $component = Livewire::test('character-viewer', [
        'publicKey' => $character->public_key,
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);
    
    // Test state to save
    $testState = [
        'hitPoints' => [true, false, true, false, true],
        'stress' => [false, true, false, true, false, false],
        'hope' => [true, true, false, false, false, false],
        'armorSlots' => [true, false, true],
        'goldHandfuls' => [true, false, true, false, true, false, true, false, true],
        'goldBags' => [false, true, false, true, false, true, false, true, false],
        'goldChest' => true,
    ];
    
    // Call the saveCharacterState method
    $component->call('saveCharacterState', $testState);
    
    // Refresh the character from database
    $character->refresh();
    
    // Assert the state was saved to the database
    $savedState = $character->character_data['interactive_state'];
    expect($savedState)->not->toBeNull();
    expect($savedState['hitPoints'])->toBe($testState['hitPoints']);
    expect($savedState['goldBags'])->toBe($testState['goldBags']);
    expect($savedState['goldHandfuls'])->toBe($testState['goldHandfuls']);
    expect($savedState['goldChest'])->toBe($testState['goldChest']);
});

test('authenticated user can load character state from database', function () {
    // Create a user and character with existing state
    $user = User::factory()->create();
    
    $existingState = [
        'hitPoints' => [true, false, true, false, true],
        'stress' => [false, true, false, true, false, false],
        'hope' => [true, true, false, false, false, false],
        'armorSlots' => [true, false, true],
        'goldHandfuls' => [true, false, true, false, true, false, true, false, true],
        'goldBags' => [false, true, false, true, false, true, false, true, false],
        'goldChest' => true,
    ];
    
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'character_data' => ['interactive_state' => $existingState],
    ]);
    
    // Act as the authenticated user
    $this->actingAs($user);
    
    // Test directly with the model method
    $characterViewer = new \App\Livewire\CharacterViewer();
    $characterViewer->character_key = $character->character_key;
    $characterViewer->can_edit = true;
    
    $result = $characterViewer->getCharacterState();
    
    // Assert the correct state was returned (check individual keys to avoid ordering issues)
    expect($result['hitPoints'])->toBe($existingState['hitPoints']);
    expect($result['stress'])->toBe($existingState['stress']);
    expect($result['hope'])->toBe($existingState['hope']);
    expect($result['armorSlots'])->toBe($existingState['armorSlots']);
    expect($result['goldHandfuls'])->toBe($existingState['goldHandfuls']);
    expect($result['goldBags'])->toBe($existingState['goldBags']);
    expect($result['goldChest'])->toBe($existingState['goldChest']);
});

test('unauthenticated user cannot save character state to database', function () {
    // Create a character without a user
    $character = Character::factory()->create(['user_id' => null]);
    
    // Create the character viewer component (not authenticated)
    $component = Livewire::test('character-viewer', [
        'publicKey' => $character->public_key,
        'characterKey' => $character->character_key,
        'canEdit' => false,
    ]);
    
    // Test state to save
    $testState = [
        'hitPoints' => [true, false, true, false, true],
        'stress' => [false, true, false, true, false, false],
        'hope' => [true, true, false, false, false, false],
        'armorSlots' => [true, false, true],
        'goldHandfuls' => [true, false, true, false, true, false, true, false, true],
        'goldBags' => [false, true, false, true, false, true, false, true, false],
        'goldChest' => true,
    ];
    
    // Call the saveCharacterState method
    $component->call('saveCharacterState', $testState);
    
    // Refresh the character from database
    $character->refresh();
    
    // Assert the state was NOT saved to the database (should be null or empty)
    expect($character->character_data['interactive_state'] ?? null)->toBeNull();
});

test('character state handles legacy goldBags arrays correctly', function () {
    // Create a user and character with legacy 5-element goldBags array
    $user = User::factory()->create();
    
    $legacyState = [
        'hitPoints' => [true, false, true, false, true],
        'stress' => [false, true, false, true, false, false],
        'hope' => [true, true, false, false, false, false],
        'armorSlots' => [true, false, true],
        'goldHandfuls' => [true, false, true, false, true], // Legacy 5-element array
        'goldBags' => [false, true, false, true, false], // Legacy 5-element array
        'goldChest' => true,
    ];
    
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'character_data' => ['interactive_state' => $legacyState],
    ]);
    
    // Act as the authenticated user
    $this->actingAs($user);
    
    // Test directly with the model method
    $characterViewer = new \App\Livewire\CharacterViewer();
    $characterViewer->character_key = $character->character_key;
    $characterViewer->can_edit = true;
    
    $result = $characterViewer->getCharacterState();
    
    // The result should still have the legacy arrays (the expansion happens in Alpine.js)
    expect($result['goldBags'])->toHaveCount(5);
    expect($result['goldHandfuls'])->toHaveCount(5);
    
    // But when we save a new state with 9 elements, it should work
    $newState = [
        'hitPoints' => [true, false, true, false, true],
        'stress' => [false, true, false, true, false, false],
        'hope' => [true, true, false, false, false, false],
        'armorSlots' => [true, false, true],
        'goldHandfuls' => [true, false, true, false, true, false, true, false, true], // 9 elements
        'goldBags' => [false, true, false, true, false, true, false, true, false], // 9 elements
        'goldChest' => true,
    ];
    
    $characterViewer->saveCharacterState($newState);
    
    // Refresh and check the new state was saved
    $character->refresh();
    expect($character->character_data['interactive_state']['goldBags'])->toHaveCount(9);
    expect($character->character_data['interactive_state']['goldHandfuls'])->toHaveCount(9);
});
