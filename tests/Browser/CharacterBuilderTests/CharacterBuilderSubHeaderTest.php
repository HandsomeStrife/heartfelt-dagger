<?php

declare(strict_types=1);

test('character builder shows sub header with title and save button', function () {
    // Create a character with a class selected
    $character = \Domain\Character\Models\Character::factory()->create([
        'name' => 'Test Character',
        'class' => 'bard',
    ]);

    $page = visit("/character-builder/{$character->character_key}");
    
    $page->wait(3)
        ->assertSee('Character Builder')
        ->assertSee('Save');
});

test('character builder shows last saved time when available', function () {
    // Create a character and update it so it has an updated_at timestamp
    $character = \Domain\Character\Models\Character::factory()->create([
        'name' => 'Test Character',
        'class' => 'bard',
    ]);
    
    // Touch the model to ensure it has an updated_at timestamp
    $character->touch();

    $page = visit("/character-builder/{$character->character_key}");
    
    $page->wait(3)
        ->assertSee('Character Builder')
        ->assertSee('Saved');
});

test('character builder shows save button when no class selected', function () {
    // Create a character without a class
    $character = \Domain\Character\Models\Character::factory()->create([
        'name' => 'Test Character',
        'class' => null,
    ]);

    $page = visit("/character-builder/{$character->character_key}");
    
    $page->wait(3)
        ->assertSee('Character Builder')
        ->assertSee('Save');
});