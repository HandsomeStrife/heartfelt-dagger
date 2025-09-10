<?php

declare(strict_types=1);

use App\Livewire\CharacterBuilder;
use Domain\Character\Models\Character;
use PHPUnit\Framework\Attributes\Test;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertArrayNotHasKey;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
test('character builder displays ancestry bonuses in stats', function () {
    // Create a character first
    $character = Character::factory()->create();

    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->call('selectClass', 'warrior')
        ->call('selectAncestry', 'simiah')
        ->call('selectCommunity', 'wildborne');

    // Verify computed stats include ancestry bonuses
    $computed_stats = $component->get('computed_stats');

    // Simiah should get +1 evasion bonus
    expect($computed_stats)->toHaveKey('evasion');
    expect($computed_stats['evasion'])->toEqual(12);
    // Base 11 + 1 from Simiah
});
test('character builder shows giant hit point bonus', function () {
    $character = createTestCharacter();
    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->call('selectClass', 'warrior')
        ->call('selectAncestry', 'giant')
        ->call('selectCommunity', 'ridgeborne');

    $computed_stats = $component->get('computed_stats');

    // Giant should get +1 hit point bonus
    expect($computed_stats)->toHaveKey('hit_points');
    expect($computed_stats['hit_points'])->toEqual(7);
    // Base 6 + 1 from Giant
});
test('character builder shows human stress bonus', function () {
    $character = createTestCharacter();
    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->call('selectClass', 'warrior')
        ->call('selectAncestry', 'human')
        ->call('selectCommunity', 'highborne');

    $computed_stats = $component->get('computed_stats');

    // Human should get +1 stress bonus
    expect($computed_stats)->toHaveKey('stress');
    expect($computed_stats['stress'])->toEqual(7);
    // Base 6 + 1 from Human
});
test('character builder shows galapa damage threshold info', function () {
    $character = createTestCharacter();
    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->call('selectClass', 'warrior')
        ->call('selectAncestry', 'galapa')
        ->call('selectCommunity', 'seaborne');

    $computed_stats = $component->get('computed_stats');

    // Galapa gets damage threshold bonus equal to proficiency
    expect($computed_stats)->toHaveKey('major_threshold');
    expect($computed_stats)->toHaveKey('severe_threshold');

    // At level 1, proficiency is 0, so thresholds get +0 bonus
    expect($computed_stats['major_threshold'])->toEqual(6);
    // 1 base armor + 0 prof + 3 + 2 from calculation
    expect($computed_stats['severe_threshold'])->toEqual(11);
    // 1 base armor + 0 prof + 8 + 2 from calculation
});
test('character builder updates stats when traits assigned', function () {
    $character = createTestCharacter();
    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->call('selectClass', 'warrior')
        ->call('selectAncestry', 'simiah')
        ->call('selectCommunity', 'wildborne')
        ->call('assignTrait', 'agility', 2);

    // Assign +2 to agility
    $computed_stats = $component->get('computed_stats');

    // Evasion should be base + ancestry bonus + trait bonus
    expect($computed_stats['evasion'])->toEqual(14);
    // Base 11 + Simiah 1 + Agility 2
});
test('character builder validates trait assignment with bonuses', function () {
    $character = createTestCharacter();
    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->call('selectClass', 'warrior')
        ->call('selectAncestry', 'simiah')
        ->call('selectCommunity', 'wildborne');

    // Assign all traits correctly
    $component->call('assignTrait', 'agility', 2)
        ->call('assignTrait', 'strength', 1)
        ->call('assignTrait', 'finesse', 1)
        ->call('assignTrait', 'instinct', 0)
        ->call('assignTrait', 'presence', 0)
        ->call('assignTrait', 'knowledge', -1);

    $computed_stats = $component->get('computed_stats');

    // Verify the stats are computed correctly with trait assignments
    expect($computed_stats['evasion'])->toEqual(14);
    // 11 + 1 (ancestry) + 2 (agility)
    expect($computed_stats['hit_points'])->toEqual(6);
    // Base warrior hit points
});
test('character builder experience functionality works', function () {
    $character = createTestCharacter();
    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->call('selectClass', 'warrior')
        ->call('selectAncestry', 'human')
        ->call('selectCommunity', 'wildborne');

    // Test adding an experience
    $component->set('new_experience_name', 'Blacksmith')
        ->set('new_experience_description', 'Skilled in metalworking')
        ->call('addExperience');

    $character = $component->get('character');

    expect($character->experiences)->toHaveCount(1);
    expect($character->experiences[0]['name'])->toEqual('Blacksmith');
    expect($character->experiences[0]['description'])->toEqual('Skilled in metalworking');
    expect($character->experiences[0]['modifier'])->toEqual(2);

    // Verify form was cleared
    expect($component->get('new_experience_name'))->toEqual('');
    expect($component->get('new_experience_description'))->toEqual('');
});
test('character builder prevents more than two experiences', function () {
    $character = createTestCharacter();
    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->call('selectClass', 'warrior')
        ->call('selectAncestry', 'human')
        ->call('selectCommunity', 'wildborne');

    // Add two experiences
    $component->set('new_experience_name', 'Blacksmith')
        ->call('addExperience')
        ->set('new_experience_name', 'Tracker')
        ->call('addExperience');

    $character = $component->get('character');
    expect($character->experiences)->toHaveCount(2);

    // Try to add a third - should be ignored
    $component->set('new_experience_name', 'Healer')
        ->call('addExperience');

    $character = $component->get('character');
    expect($character->experiences)->toHaveCount(2);
    // Still only 2
});
test('character builder can remove experiences', function () {
    $character = createTestCharacter();
    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->call('selectClass', 'warrior')
        ->call('selectAncestry', 'human')
        ->call('selectCommunity', 'wildborne');

    // Add an experience
    $component->set('new_experience_name', 'Blacksmith')
        ->call('addExperience');

    $character = $component->get('character');
    expect($character->experiences)->toHaveCount(1);

    // Remove the experience
    $component->call('removeExperience', 0);

    $character = $component->get('character');
    expect($character->experiences)->toHaveCount(0);
});
test('character builder displays bonuses in ui', function () {
    $character = createTestCharacter();
    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->call('selectClass', 'warrior')
        ->call('selectAncestry', 'simiah')
        ->call('selectCommunity', 'wildborne');

    $ancestry_bonuses = $component->get('ancestry_bonuses');

    // Check that Simiah evasion bonus exists
    expect($ancestry_bonuses)->toHaveKey('evasion');
    expect($ancestry_bonuses['evasion'])->toEqual(1);

    // Other bonuses should not be present (getAncestryBonuses only returns non-zero bonuses)
    assertArrayNotHasKey('hit_points', $ancestry_bonuses);
    assertArrayNotHasKey('stress', $ancestry_bonuses);
    assertArrayNotHasKey('damage_thresholds', $ancestry_bonuses);
});
test('character builder updates progress with all bonuses', function () {
    $character = createTestCharacter();
    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->call('selectClass', 'warrior')
        ->call('selectAncestry', 'giant')
        ->call('selectCommunity', 'ridgeborne');

    // Complete trait assignment
    $component->call('assignTrait', 'agility', 1)
        ->call('assignTrait', 'strength', 2)
        ->call('assignTrait', 'finesse', 0)
        ->call('assignTrait', 'instinct', 0)
        ->call('assignTrait', 'presence', 1)
        ->call('assignTrait', 'knowledge', -1);

    $computed_stats = $component->get('computed_stats');

    // Verify Giant ancestry bonus is applied
    expect($computed_stats['hit_points'])->toEqual(7);
    // Base 6 + Giant 1
    expect($computed_stats['evasion'])->toEqual(12);
    // Base 11 + Agility 1
});
test('character builder computed stats property includes all bonuses', function () {
    $character = createTestCharacter();
    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->call('selectClass', 'bard') // Different class for variety
        ->call('selectAncestry', 'human')
        ->call('selectCommunity', 'highborne');

    $computed_stats = $component->get('computed_stats');

    // Should include ancestry bonuses
    expect($computed_stats)->toBeArray();
    expect($computed_stats)->toHaveKey('evasion');
    expect($computed_stats)->toHaveKey('hit_points');
    expect($computed_stats)->toHaveKey('stress');
    expect($computed_stats)->toHaveKey('major_threshold');
    expect($computed_stats)->toHaveKey('severe_threshold');

    // Human gets +1 stress
    expect($computed_stats['stress'])->toEqual(7);
    // Base 6 + Human 1
});
test('character builder saves and loads with ancestry bonuses', function () {
    // Create and save a character
    $character = createTestCharacter();
    $component = livewire(CharacterBuilder::class, ['characterKey' => $character->character_key])
        ->call('selectClass', 'warrior')
        ->call('selectAncestry', 'simiah')
        ->call('selectCommunity', 'wildborne')
        ->call('saveCharacter');

    $character_key = $component->get('character')->character_key;

    // Load the character in a new component
    $newComponent = livewire(CharacterBuilder::class, ['characterKey' => $character_key]);

    $computed_stats = $newComponent->get('computed_stats');
    $ancestry_bonuses = $newComponent->get('ancestry_bonuses');

    // Verify ancestry bonuses are preserved
    expect($ancestry_bonuses['evasion'])->toEqual(1);
    expect($computed_stats['evasion'])->toEqual(12);
    // Should include Simiah bonus
});
