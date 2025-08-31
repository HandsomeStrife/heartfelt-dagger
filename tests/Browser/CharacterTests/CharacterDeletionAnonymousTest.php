<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

test('anonymous user can access character builder', function () {
    $page = visit('/character-builder');
    
    $page->wait(3)
        ->assertSee('Character Builder')
        ->assertSee('Choose a Class');
});

test('anonymous user can access characters page', function () {
    $page = visit('/characters');
    
    $page->wait(3)
        ->assertSee('Characters'); // Should see characters page content
});

test('anonymous character can be viewed', function () {
    // Create an anonymous character directly in the database
    $character = Character::factory()->create([
        'user_id' => null, // Anonymous character
        'name' => 'Test Anonymous Character',
        'class' => 'druid',
    ]);
    
    $page = visit("/character/{$character->public_key}");
    
    $page->wait(3)
        ->assertSee('Test Anonymous Character');
});
