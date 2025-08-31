<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

test('characters page loads correctly', function () {
    $page = visit('/characters');
    
    $page->wait(3)
        ->assertSee('Characters'); // Should see some character-related content
});

test('can create and view character', function () {
    // Create an anonymous character
    $character = Character::factory()->create([
        'user_id' => null,
        'name' => 'Test Character',
        'class' => 'warrior',
    ]);
    
    // Visit the character page
    $page = visit("/character/{$character->public_key}");
    
    $page->wait(3)
        ->assertSee('Test Character');
});
