<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('level 2 character can create experience through tier achievements', function () {
    $character = Character::factory()->create([
        'level' => 1,
        'class' => 'warrior',
        'is_public' => true,
        'user_id' => null, // Public character
    ]);

    // Create a Livewire component instance for the level up
    $component = Livewire\Livewire::test(\App\Livewire\CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);

    // Verify the component loads correctly
    $component->assertSet('character.level', 1);

    // Add a tier experience
    $component->set('new_experience_name', 'Combat Veteran')
        ->set('new_experience_description', 'Years of battlefield experience')
        ->call('addTierExperience');

    // Verify the experience was stored in advancement_choices
    $component->assertSet('advancement_choices.tier_experience.name', 'Combat Veteran')
        ->assertSet('advancement_choices.tier_experience.description', 'Years of battlefield experience')
        ->assertSet('advancement_choices.tier_experience.modifier', 2);

    // Verify form was cleared
    $component->assertSet('new_experience_name', '')
        ->assertSet('new_experience_description', '');
});

test('experience creation requires name', function () {
    $character = Character::factory()->create([
        'level' => 1,
        'class' => 'warrior',
        'is_public' => true,
        'user_id' => null,
    ]);

    $component = Livewire\Livewire::test(\App\Livewire\CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);

    // Try to add experience without name
    $component->set('new_experience_description', 'Some description')
        ->call('addTierExperience');

    // Verify no tier experience was created when name is empty
    expect($component->get('advancement_choices')['tier_experience'] ?? null)->toBeNull();
});

test('tier experience can be removed', function () {
    $character = Character::factory()->create([
        'level' => 1,
        'class' => 'warrior',
        'is_public' => true,
        'user_id' => null,
    ]);

    $component = Livewire\Livewire::test(\App\Livewire\CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);

    // Add experience
    $component->set('new_experience_name', 'Test Experience')
        ->call('addTierExperience');

    // Verify it's stored
    $component->assertSet('advancement_choices.tier_experience.name', 'Test Experience');

    // Remove it
    $component->call('removeTierExperience');

    // Verify it's removed
    expect($component->get('advancement_choices')['tier_experience'] ?? null)->toBeNull();
});

test('tier experience is created in database on level up completion', function () {
    $character = Character::factory()->create([
        'level' => 1,
        'class' => 'warrior',
        'is_public' => true,
        'user_id' => null,
    ]);

    // Verify no experiences exist initially
    expect($character->experiences()->count())->toBe(0);

    $component = Livewire\Livewire::test(\App\Livewire\CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);

    // Add tier experience
    $component->set('new_experience_name', 'Battlefield Tactics')
        ->set('new_experience_description', 'Strategic combat knowledge')
        ->call('addTierExperience');

    // Set required advancement selections (skip for this test by mocking available slots)
    $component->set('available_slots', []);  // No advancement slots needed

    // Complete level up
    $component->call('confirmLevelUp');

    // Refresh character from database
    $character->refresh();

    // Verify character leveled up
    expect($character->level)->toBe(2);

    // Verify experience was created
    $experiences = $character->experiences;
    expect($experiences)->toHaveCount(1);

    $experience = $experiences->first();
    expect($experience->experience_name)->toBe('Battlefield Tactics');
    expect($experience->experience_description)->toBe('Strategic combat knowledge');
    expect($experience->modifier)->toBe(2);
});
