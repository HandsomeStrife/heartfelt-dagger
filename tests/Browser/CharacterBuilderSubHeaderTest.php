<?php

declare(strict_types=1);

test('character builder shows black sub header with title preview and save buttons', function () {
    // Create a character with a class selected
    $character = \Domain\Character\Models\Character::factory()->create([
        'name' => 'Test Character',
        'class' => 'bard',
    ]);

    $page = visit("/character-builder/{$character->character_key}");
    
    $page
        // Check that the sub-navigation component exists
        ->assertPresent('x-sub-navigation')
        
        // Check that the title is in the sub-header
        ->assertSeeIn('x-sub-navigation h1', 'Character Builder')
        
        // Check that both Save and Preview buttons are in the sub-header
        ->assertSeeIn('x-sub-navigation', 'Save')
        ->assertPresent('x-sub-navigation [dusk="save-character-button"]')
        ->assertSeeIn('x-sub-navigation', 'Preview')
        ->assertPresent('x-sub-navigation [dusk="preview-character-button"]');
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
    
    $page
        // Check that the last saved time is displayed
        ->assertSeeIn('x-sub-navigation', 'Saved')
        ->assertSeeIn('x-sub-navigation', 'just now'); // Wait for JavaScript to calculate time
});

test('character builder shows save button but hides preview when no class selected', function () {
    // Create a character without a class
    $character = \Domain\Character\Models\Character::factory()->create([
        'name' => 'Test Character',
        'class' => null,
    ]);

    $page = visit("/character-builder/{$character->character_key}");
    
    $page
        // Check that the Save button is present but Preview button is not
        ->assertPresent('x-sub-navigation [dusk="save-character-button"]')
        ->assertSeeIn('x-sub-navigation', 'Save')
        ->assertMissing('x-sub-navigation [dusk="preview-character-button"]');
});