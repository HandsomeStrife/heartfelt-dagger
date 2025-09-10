<?php

declare(strict_types=1);

use App\Livewire\CharacterLevelUp;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterExperience;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('experience bonus selection shows character experiences', function () {
    $character = Character::factory()->create([
        'level' => 1,
        'class' => 'warrior',
        'is_public' => true,
    ]);

    // Create some experiences for the character
    CharacterExperience::factory()->create([
        'character_id' => $character->id,
        'experience_name' => 'Combat Training',
        'modifier' => 2,
    ]);

    CharacterExperience::factory()->create([
        'character_id' => $character->id,
        'experience_name' => 'Wilderness Survival',
        'modifier' => 2,
    ]);

    $component = Livewire::test(CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);

    // Refresh character to include experiences
    $character = $character->fresh();
    expect($character->experiences)->toHaveCount(2);
    expect($character->experiences->pluck('name')->toArray())->toContain('Combat Training', 'Wilderness Survival');
});

test('experience bonus can be selected for advancement', function () {
    $character = Character::factory()->create([
        'level' => 1,
        'class' => 'warrior',
        'is_public' => true,
    ]);

    // Create some experiences
    CharacterExperience::factory()->create([
        'character_id' => $character->id,
        'experience_name' => 'Combat Training',
        'modifier' => 2,
    ]);

    CharacterExperience::factory()->create([
        'character_id' => $character->id,
        'experience_name' => 'Wilderness Survival',
        'modifier' => 2,
    ]);

    $component = Livewire::test(CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);

    // Select experiences for bonus for advancement index 0
    $component->call('selectExperienceBonus', 0, 'Combat Training');
    $component->call('selectExperienceBonus', 0, 'Wilderness Survival');

    // Should update advancement choices
    $choices = $component->get('advancement_choices');
    expect($choices[0]['experience_bonuses'])->toBe(['Combat Training', 'Wilderness Survival']);
});

test('experience bonus selection is limited to 2 experiences', function () {
    $character = Character::factory()->create([
        'level' => 1,
        'class' => 'warrior',
        'is_public' => true,
    ]);

    // Create three experiences
    CharacterExperience::factory()->create([
        'character_id' => $character->id,
        'experience_name' => 'Combat Training',
        'modifier' => 2,
    ]);

    CharacterExperience::factory()->create([
        'character_id' => $character->id,
        'experience_name' => 'Wilderness Survival',
        'modifier' => 2,
    ]);

    CharacterExperience::factory()->create([
        'character_id' => $character->id,
        'experience_name' => 'Social Skills',
        'modifier' => 2,
    ]);

    $component = Livewire::test(CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);

    // Try to select all three experiences
    $component->call('selectExperienceBonus', 0, 'Combat Training');
    $component->call('selectExperienceBonus', 0, 'Wilderness Survival');
    $component->call('selectExperienceBonus', 0, 'Social Skills'); // Should be ignored (over limit)

    // Should only have 2 selected
    $choices = $component->get('advancement_choices');
    expect($choices[0]['experience_bonuses'])->toHaveCount(2);
    expect($choices[0]['experience_bonuses'])->toBe(['Combat Training', 'Wilderness Survival']);
});

test('experience bonus can be deselected', function () {
    $character = Character::factory()->create([
        'level' => 1,
        'class' => 'warrior',
        'is_public' => true,
    ]);

    CharacterExperience::factory()->create([
        'character_id' => $character->id,
        'experience_name' => 'Combat Training',
        'modifier' => 2,
    ]);

    $component = Livewire::test(CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);

    // Select and then deselect an experience
    $component->call('selectExperienceBonus', 0, 'Combat Training');
    $component->call('selectExperienceBonus', 0, 'Combat Training'); // Toggle off

    // Should be empty
    $choices = $component->get('advancement_choices');
    expect($choices[0]['experience_bonuses'])->toBeEmpty();
});

test('experience bonus selection respects can_edit permission', function () {
    $character = Character::factory()->create([
        'level' => 1,
        'class' => 'warrior',
        'is_public' => true,
    ]);

    CharacterExperience::factory()->create([
        'character_id' => $character->id,
        'experience_name' => 'Combat Training',
        'modifier' => 2,
    ]);

    $component = Livewire::test(CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => false, // Cannot edit
    ]);

    // Try to select an experience
    $component->call('selectExperienceBonus', 0, 'Combat Training');

    // Should not update advancement choices
    $choices = $component->get('advancement_choices');
    expect($choices[0]['experience_bonuses'] ?? [])->toBeEmpty();
});
