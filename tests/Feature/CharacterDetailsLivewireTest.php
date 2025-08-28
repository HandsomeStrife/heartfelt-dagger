<?php

declare(strict_types=1);

use App\Livewire\CharacterBuilder;
use Domain\Character\Models\Character;
use function Pest\Livewire\livewire;

test('character name auto-saves when updated via live model binding', function () {
    // Create a character in the database
    $character = Character::factory()->create([
        'name' => 'Original Name',
        'pronouns' => 'they/them',
        'class' => 'warrior',
    ]);

    // Test the Livewire component
    livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->assertSet('character.name', 'Original Name')
        ->set('character.name', 'Updated Name via Live Model')
        ->assertDispatched('notify')
        ->assertSet('character.name', 'Updated Name via Live Model');

    // Verify the database was updated
    $character->refresh();
    expect($character->name)->toBe('Updated Name via Live Model');
});

test('pronouns auto-save when updated via live model binding', function () {
    // Create a character in the database
    $character = Character::factory()->create([
        'name' => 'Test Character',
        'pronouns' => 'he/him',
        'class' => 'warrior',
    ]);

    // Test the Livewire component
    livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->assertSet('pronouns', 'he/him')
        ->set('pronouns', 'she/her')
        ->assertDispatched('notify')
        ->assertSet('pronouns', 'she/her');

    // Verify the database was updated
    $character->refresh();
    expect($character->pronouns)->toBe('she/her');
});

test('character name and pronouns can be updated independently', function () {
    // Create a character in the database
    $character = Character::factory()->create([
        'name' => 'Independent Test',
        'pronouns' => 'xe/xir',
        'class' => 'wizard',
    ]);

    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->assertSet('character.name', 'Independent Test')
        ->assertSet('pronouns', 'xe/xir');

    // Update only the name
    $component->set('character.name', 'Name Only Update')
        ->assertSet('character.name', 'Name Only Update')
        ->assertSet('pronouns', 'xe/xir');

    // Update only the pronouns
    $component->set('pronouns', 'they/them')
        ->assertSet('character.name', 'Name Only Update')
        ->assertSet('pronouns', 'they/them');

    // Verify both changes were saved to database
    $character->refresh();
    expect($character->name)->toBe('Name Only Update');
    expect($character->pronouns)->toBe('they/them');
});

test('empty character name is handled gracefully', function () {
    // Create a character in the database
    $character = Character::factory()->create([
        'name' => 'Test Character',
        'pronouns' => 'any/all',
        'class' => 'bard',
    ]);

    // Test setting name to empty string
    livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->assertSet('character.name', 'Test Character')
        ->set('character.name', '')
        ->assertSet('character.name', '');

    // Verify the database was updated (empty name should be saved)
    $character->refresh();
    expect($character->name)->toBe('');
});

test('long character name is handled correctly within database limits', function () {
    // Create a character in the database
    $character = Character::factory()->create([
        'name' => 'Short Name',
        'class' => 'ranger',
    ]);

    // Create a name that's exactly 100 characters (database limit)
    $longName = str_pad('Very Long Character Name With Extra Text To Test Maximum Length Handling', 100, 'X');

    // Test setting a long name
    livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->assertSet('character.name', 'Short Name')
        ->set('character.name', $longName)
        ->assertSet('character.name', $longName);

    // Verify the database was updated
    $character->refresh();
    expect($character->name)->toBe($longName);
    expect(strlen($character->name))->toBe(100);
});

test('special characters in name and pronouns are preserved', function () {
    // Create a character in the database
    $character = Character::factory()->create([
        'name' => 'Regular Name',
        'pronouns' => 'they/them',
        'class' => 'sorcerer',
    ]);

    $specialName = "Àlex O'Reilly-Smith & Co. (3rd)";
    $specialPronouns = "ze/zir/zirs";

    // Test special characters
    livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->set('character.name', $specialName)
        ->set('pronouns', $specialPronouns)
        ->assertSet('character.name', $specialName)
        ->assertSet('pronouns', $specialPronouns);

    // Verify the database preserves special characters
    $character->refresh();
    expect($character->name)->toBe($specialName);
    expect($character->pronouns)->toBe($specialPronouns);
});
