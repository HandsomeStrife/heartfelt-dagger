<?php

declare(strict_types=1);

use App\Livewire\CharacterBuilder;
use Domain\Character\Models\Character;

use function Pest\Livewire\livewire;

test('background answers auto-save when updated via live model binding', function () {
    // Create a character in the database
    $character = Character::factory()->create([
        'name' => 'Background Test Hero',
        'class' => 'warrior',
    ]);

    // Test the Livewire component with background answers
    livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->set('character.background_answers.0', 'I learned courage from my mentor who taught me to face my fears.')
        ->set('character.background_answers.1', 'My greatest loss was when my village was destroyed by raiders.')
        ->set('character.background_answers.2', 'I admire the legendary warrior who saved my people.')
        ->assertSet('character.background_answers.0', 'I learned courage from my mentor who taught me to face my fears.')
        ->assertSet('character.background_answers.1', 'My greatest loss was when my village was destroyed by raiders.')
        ->assertSet('character.background_answers.2', 'I admire the legendary warrior who saved my people.');

    // Verify the database was updated
    $character->refresh();
    $characterData = $character->character_data;
    expect($characterData['background']['answers'][0])->toBe('I learned courage from my mentor who taught me to face my fears.');
    expect($characterData['background']['answers'][1])->toBe('My greatest loss was when my village was destroyed by raiders.');
    expect($characterData['background']['answers'][2])->toBe('I admire the legendary warrior who saved my people.');
});

test('character details auto-save when updated via live model binding', function () {
    // Create a character in the database
    $character = Character::factory()->create([
        'name' => 'Details Test Hero',
        'class' => 'bard',
    ]);

    // Test the character detail fields
    livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->set('character.physical_description', 'Tall and lean with piercing blue eyes.')
        ->set('character.personality_traits', 'Witty and charming but secretly insecure.')
        ->set('character.personal_history', 'Grew up in a traveling troupe of performers.')
        ->set('character.motivations', 'Wants to prove worthy of their family legacy.')
        ->assertSet('character.physical_description', 'Tall and lean with piercing blue eyes.')
        ->assertSet('character.personality_traits', 'Witty and charming but secretly insecure.')
        ->assertSet('character.personal_history', 'Grew up in a traveling troupe of performers.')
        ->assertSet('character.motivations', 'Wants to prove worthy of their family legacy.');

    // Verify the database was updated
    $character->refresh();
    $characterData = $character->character_data;
    expect($characterData['background']['physicalDescription'])->toBe('Tall and lean with piercing blue eyes.');
    expect($characterData['background']['personalityTraits'])->toBe('Witty and charming but secretly insecure.');
    expect($characterData['background']['personalHistory'])->toBe('Grew up in a traveling troupe of performers.');
    expect($characterData['background']['motivations'])->toBe('Wants to prove worthy of their family legacy.');
});

test('background answers update independently', function () {
    // Create a character in the database
    $character = Character::factory()->create([
        'name' => 'Independent Background Test',
        'class' => 'wizard',
    ]);

    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key]);

    // Set first background answer
    $component->set('character.background_answers.0', 'First answer about my magical training.')
        ->assertSet('character.background_answers.0', 'First answer about my magical training.');

    // Set second background answer independently
    $component->set('character.background_answers.1', 'Second answer about my lost spellbook.')
        ->assertSet('character.background_answers.0', 'First answer about my magical training.')
        ->assertSet('character.background_answers.1', 'Second answer about my lost spellbook.');

    // Update first answer and verify second is preserved
    $component->set('character.background_answers.0', 'Updated first answer about advanced studies.')
        ->assertSet('character.background_answers.0', 'Updated first answer about advanced studies.')
        ->assertSet('character.background_answers.1', 'Second answer about my lost spellbook.');

    // Verify both changes were saved to database
    $character->refresh();
    $characterData = $character->character_data;
    expect($characterData['background']['answers'][0])->toBe('Updated first answer about advanced studies.');
    expect($characterData['background']['answers'][1])->toBe('Second answer about my lost spellbook.');
});

test('empty background answers are handled gracefully', function () {
    // Create a character in the database
    $character = Character::factory()->create([
        'name' => 'Empty Test Hero',
        'class' => 'ranger',
    ]);

    // Test setting answers to empty strings
    livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->set('character.background_answers.0', 'Some initial answer.')
        ->set('character.background_answers.0', '')
        ->assertSet('character.background_answers.0', '');

    // Verify the database was updated (empty answer should be saved)
    $character->refresh();
    $characterData = $character->character_data;
    expect($characterData['background']['answers'][0])->toBe('');
});

test('mark background complete functionality works', function () {
    // Create a character in the database
    $character = Character::factory()->create([
        'name' => 'Complete Test Hero',
        'class' => 'guardian',
    ]);

    // Add at least one background answer to enable completion
    livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->set('character.background_answers.0', 'I was trained by the city guard.')
        ->call('markBackgroundComplete')
        ->assertDispatched('notify');

    // Verify the character step completion was saved
    $character->refresh();
    $characterData = $character->character_data;
    expect($characterData['manualStepCompletions'])->toContain('background');
});
