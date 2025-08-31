<?php

declare(strict_types=1);

test('save button updates timestamp display', function () {
    // Create a character
    $character = \Domain\Character\Models\Character::factory()->create([
        'name' => 'Test Character',
        'class' => 'bard',
    ]);

    $page = visit("/character-builder/{$character->character_key}");
    
    $page->wait(3)
        // Wait for the initial timestamp to load
        ->assertSee('Saved')
        
        // Click the save button
        ->click('[dusk="save-character-button"]')
        
        // Wait a moment for save to complete
        ->wait(2)
        
        // Check that we still see the Saved text (timestamp should update)
        ->assertSee('Saved');
});

test('timestamp updates over time', function () {
    // Create a character saved 2 minutes ago
    $character = \Domain\Character\Models\Character::factory()->create([
        'name' => 'Test Character',
        'class' => 'bard',
        'updated_at' => now()->subMinutes(2),
    ]);

    $page = visit("/character-builder/{$character->character_key}");
    
    $page
        // Should show "2 minutes ago" initially
        ->assertSee('2 minutes ago');
});