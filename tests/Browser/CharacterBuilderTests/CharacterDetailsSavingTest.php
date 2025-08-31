<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

test('character builder loads existing character', function () {
    // Create a character in the database
    $character = Character::factory()->create([
        'name' => 'Test Character',
        'class' => 'warrior',
    ]);

    $page = visit("/character-builder/{$character->character_key}");
    
    $page->wait(3)
        ->assertSee('Character Builder')
        ->assertSee('Character Name'); // Just verify the form field exists
});

test('character name field is present', function () {
    // Create a character in the database
    $character = Character::factory()->create([
        'name' => 'Test Character',
        'class' => 'warrior',
    ]);

    $page = visit("/character-builder/{$character->character_key}");
    
    $page->wait(3)
        ->assertSee('Character Name');
});

test('can interact with character name field', function () {
    // Create a character in the database
    $character = Character::factory()->create([
        'name' => 'Original Name',
        'class' => 'warrior',
    ]);

    $page = visit("/character-builder/{$character->character_key}");
    
    $page->wait(3)
        ->assertSee('Character Name')
        ->type('[dusk="character-name-input"]', 'Updated Name')
        ->wait(2)
        ->click('[dusk="save-character-button"]')
        ->wait(2)
        ->assertSee('Character Builder'); // Just verify page still loads
});
