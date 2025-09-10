<?php

declare(strict_types=1);
use App\Livewire\CharacterBuilder;
use Domain\Character\Models\Character;
use PHPUnit\Framework\Attributes\Test;

use function Pest\Livewire\livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('character builder shows clank bonus functionality for clank ancestry', function () {
    $character = createTestCharacter();

    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->call('selectClass', 'warrior')
        ->call('selectAncestry', 'clank')
        ->call('selectCommunity', 'wildborne')
        ->call('addExperience', 'Blacksmith', 'Working with metal and tools');

    $component->assertSee('Click to select for your Clank heritage bonus (+3)');
});
test('character builder does not show clank bonus for other ancestries', function () {
    $character = createTestCharacter();

    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->call('selectClass', 'warrior')
        ->call('selectAncestry', 'human')
        ->call('selectCommunity', 'highborne');

    $component->assertDontSee('As a Clank, you can select one experience for a +3 modifier (Purposeful Design)');
});
test('character builder can select clank bonus experience', function () {
    $character = createTestCharacter();

    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->call('selectClass', 'warrior')
        ->call('selectAncestry', 'clank')
        ->call('selectCommunity', 'wildborne')
        ->call('addExperience', 'Blacksmith', 'Working with metal and tools')
        ->call('selectClankBonusExperience', 'Blacksmith');

    // Check that the bonus experience was set
    expect($component->get('character.clank_bonus_experience'))->toEqual('Blacksmith');
});
test('character builder shows enhanced modifier for clank bonus experience', function () {
    $character = createTestCharacter();

    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->call('selectClass', 'warrior')
        ->call('selectAncestry', 'clank')
        ->call('selectCommunity', 'wildborne')
        ->call('addExperience', 'Blacksmith', 'Working with metal and tools')
        ->call('addExperience', 'Silver Tongue', 'Persuasive speaking')
        ->call('selectClankBonusExperience', 'Blacksmith');

    // The selected experience should show +3, the other should show +2
    $component->assertSee('Clank Bonus');
    $component->assertSee('includes +1 from Clank Purposeful Design');
});
test('character builder non clank cannot select bonus experience', function () {
    $character = createTestCharacter();

    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->call('selectClass', 'warrior')
        ->call('selectAncestry', 'human')
        ->call('selectCommunity', 'highborne')
        ->call('addExperience', 'Blacksmith', 'Working with metal and tools')
        ->call('selectClankBonusExperience', 'Blacksmith');

    // Non-clank should not have the bonus set
    expect($component->get('character.clank_bonus_experience'))->toBeNull();
});
test('character builder clank bonus persists through save and load', function () {
    $character = createTestCharacter();

    // Set up character with Clank bonus
    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->call('selectClass', 'warrior')
        ->call('selectAncestry', 'clank')
        ->call('selectCommunity', 'wildborne')
        ->call('addExperience', 'Blacksmith', 'Working with metal and tools')
        ->call('selectClankBonusExperience', 'Blacksmith');

    $character_key = $component->get('character.character_key');

    // Load in a new component instance
    $newComponent = livewire(CharacterBuilder::class, ['characterKey' => $character_key]);

    // Verify the bonus experience is preserved
    expect($newComponent->get('character.clank_bonus_experience'))->toEqual('Blacksmith');
    expect($newComponent->get('character.selected_ancestry'))->toEqual('clank');
});
test('character builder clank experience modifier calculation', function () {
    $character = createTestCharacter();

    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->call('selectClass', 'warrior')
        ->call('selectAncestry', 'clank')
        ->call('selectCommunity', 'wildborne')
        ->call('addExperience', 'Blacksmith', 'Working with metal and tools')
        ->call('addExperience', 'Silver Tongue', 'Persuasive speaking')
        ->call('selectClankBonusExperience', 'Blacksmith');

    $character_data = $component->get('character');

    // Test modifier calculation
    expect($character_data->getExperienceModifier('Blacksmith'))->toEqual(3);
    expect($character_data->getExperienceModifier('Silver Tongue'))->toEqual(2);
    expect($character_data->getExperienceModifier('NonExistent'))->toEqual(2);
});
