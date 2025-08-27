<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

test('character details save and persist correctly', function () {
    // Create a character in the database
    $character = Character::factory()->create([
        'name' => 'Initial Character',
        'pronouns' => null,
        'class' => 'warrior',
    ]);

    $page = visit("/character-builder/{$character->character_key}");
    
    $page
            ->waitForCharacterBuilderToLoad()
            
            // Verify the character name persisted
            ->assertPresent('[dusk="character-name-input"]')
            ->assertValue('[dusk="character-name-input"]', 'Updated Hero Name')
            
            // Verify the pronouns persisted  
            ->assertPresent('#character-pronouns')
            ->assertValue('#character-pronouns', 'they/them');
});

test('character name updates in real time', function () {
    // Create a character in the database
    $character = Character::factory()->create([
        'name' => 'Test Character',
        'class' => 'warrior',
    ]);

    $page = visit("/character-builder/{$character->character_key}");
    
    $page
            ->waitForCharacterBuilderToLoad()
            
            // Update character name
            ->assertPresent('[dusk="character-name-input"]')
            ->clear('[dusk="character-name-input"]')
            ->type('[dusk="character-name-input"]', 'Real Time Hero')
            ->wait(2000) // Wait for Livewire to process the update
            
            // Check if the name appears in the character summary or header
            ->assertSee('Real Time Hero');
});

test('pronouns field updates independently', function () {
    // Create a character in the database
    $character = Character::factory()->create([
        'name' => 'Pronoun Test',
        'pronouns' => 'he/him',
        'class' => 'warrior',
    ]);

    $page = visit("/character-builder/{$character->character_key}");
    
    $page
            ->waitForCharacterBuilderToLoad()
            
            // Verify initial pronouns are loaded
            ->assertPresent('#character-pronouns')
            ->assertValue('#character-pronouns', 'he/him')
            
            // Update pronouns
            ->clear('#character-pronouns')
            ->type('#character-pronouns', 'she/her')
            ->wait(2000) // Wait for Livewire to process
            
            // Manually save to ensure database update
            ->click('[dusk="save-character-button"]')
            ->assertSee('Character saved successfully!');
    });

    // Verify pronouns were saved to database
    $character->refresh();
    expect($character->pronouns)->toBe('she/her');
});

test('character details autosave without explicit save button', function () {
    // Create a character in the database
    $character = Character::factory()->create([
        'name' => 'Autosave Test',
        'class' => 'warrior',
    ]);

    $page = visit("/character-builder/{$character->character_key}");
    
    $page
            ->waitForCharacterBuilderToLoad()
            
            // Update character name
            ->assertPresent('[dusk="character-name-input"]')
            ->clear('[dusk="character-name-input"]')
            ->type('[dusk="character-name-input"]', 'Autosaved Hero')
            ->wait(3000) // Wait longer for potential autosave
            
            // Update pronouns
            ->assertPresent('#character-pronouns')
            ->clear('#character-pronouns')
            ->type('#character-pronouns', 'xe/xir')
            ->wait(3000); // Wait longer for potential autosave
    });

    // Check if the data was automatically saved without clicking save button
    $character->refresh();
    
    // These assertions will help us understand if autosave is working
    dump("Character name after autosave test: " . $character->name);
    dump("Character pronouns after autosave test: " . $character->pronouns);
    
    // We expect these to be updated if autosave is working
    expect($character->name)->toBe('Autosaved Hero');
    expect($character->pronouns)->toBe('xe/xir');
