<?php

declare(strict_types=1);

use Domain\Character\Actions\LoadCharacterAction;
use Domain\Character\Actions\SaveCharacterAction;
use Domain\Character\Data\CharacterBuilderData;
use Domain\Character\Models\Character;

test('character name updates correctly in database', function () {
    // Create a character in the database
    $character = Character::factory()->create([
        'name' => 'Original Name',
        'pronouns' => 'they/them',
        'class' => 'warrior',
    ]);

    // Load character data
    $loadAction = new LoadCharacterAction;
    $characterData = $loadAction->execute($character->character_key);

    // Update the character name
    $characterData->name = 'Updated Name';

    // Save using the action
    $saveAction = new SaveCharacterAction;
    $updatedCharacter = $saveAction->updateCharacter($character, $characterData, 'they/them');

    // Verify the name was updated
    expect($updatedCharacter->name)->toBe('Updated Name');
    expect($updatedCharacter->pronouns)->toBe('they/them');

    // Verify the database was updated
    $character->refresh();
    expect($character->name)->toBe('Updated Name');
    expect($character->pronouns)->toBe('they/them');
});

test('pronouns update correctly in database', function () {
    // Create a character in the database
    $character = Character::factory()->create([
        'name' => 'Test Character',
        'pronouns' => 'he/him',
        'class' => 'warrior',
    ]);

    // Load character data
    $loadAction = new LoadCharacterAction;
    $characterData = $loadAction->execute($character->character_key);

    // Update pronouns
    $newPronouns = 'she/her';

    // Save using the action
    $saveAction = new SaveCharacterAction;
    $updatedCharacter = $saveAction->updateCharacter($character, $characterData, $newPronouns);

    // Verify the pronouns were updated
    expect($updatedCharacter->pronouns)->toBe('she/her');

    // Verify the database was updated
    $character->refresh();
    expect($character->pronouns)->toBe('she/her');
});

test('character name and pronouns update independently', function () {
    // Create a character in the database
    $character = Character::factory()->create([
        'name' => 'Initial Name',
        'pronouns' => 'xe/xir',
        'class' => 'wizard',
    ]);

    // Load character data
    $loadAction = new LoadCharacterAction;
    $characterData = $loadAction->execute($character->character_key);

    // Update only the name
    $characterData->name = 'Name Only Update';

    // Save with existing pronouns
    $saveAction = new SaveCharacterAction;
    $updatedCharacter = $saveAction->updateCharacter($character, $characterData, 'xe/xir');

    // Verify only name changed
    expect($updatedCharacter->name)->toBe('Name Only Update');
    expect($updatedCharacter->pronouns)->toBe('xe/xir');

    // Now update only pronouns
    $newPronouns = 'they/them';
    $updatedCharacter2 = $saveAction->updateCharacter($character, $characterData, $newPronouns);

    // Verify pronouns changed but name stayed the same
    expect($updatedCharacter2->name)->toBe('Name Only Update');
    expect($updatedCharacter2->pronouns)->toBe('they/them');

    // Final database verification
    $character->refresh();
    expect($character->name)->toBe('Name Only Update');
    expect($character->pronouns)->toBe('they/them');
});

test('character details persist after reload', function () {
    // Create and save character with specific details
    $character = Character::factory()->create([
        'name' => 'Persistence Test',
        'pronouns' => 'any/all',
        'class' => 'bard',
    ]);

    // Load character data
    $loadAction = new LoadCharacterAction;
    $characterData = $loadAction->execute($character->character_key);

    // Verify loaded data matches database
    expect($characterData->name)->toBe('Persistence Test');

    // Note: LoadCharacterAction might not load pronouns into CharacterBuilderData
    // So we test this separately by checking the character model
    expect($character->pronouns)->toBe('any/all');

    // Update and save
    $characterData->name = 'Persistence Updated';
    $newPronouns = 'fae/faer';

    $saveAction = new SaveCharacterAction;
    $saveAction->updateCharacter($character, $characterData, $newPronouns);

    // Simulate a fresh load (like page reload)
    $freshLoadAction = new LoadCharacterAction;
    $freshCharacterData = $freshLoadAction->execute($character->character_key);

    // Verify persistence
    expect($freshCharacterData->name)->toBe('Persistence Updated');

    // Check pronouns from database
    $character->refresh();
    expect($character->pronouns)->toBe('fae/faer');
});
