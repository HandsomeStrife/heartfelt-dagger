<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Four-Step Level Up Workflow Tests', function () {

    test('level up workflow progresses through all four steps correctly', function () {
        $character = Character::factory()->create([
            'level' => 1,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        $component = Livewire::test(\App\Livewire\CharacterLevelUp::class, [
            'characterKey' => $character->character_key,
            'canEdit' => true,
        ]);

        // Should start at tier_achievements
        $component->assertSet('current_step', 'tier_achievements');

        // Should have available advancement slots
        $availableSlots = $component->get('available_slots');
        expect($availableSlots)->toHaveCount(2); // Tier 2 has 2 advancement slots

        // Should have tier options loaded
        $tierOptions = $component->get('tier_options');
        expect($tierOptions)->toHaveKey('options');
        expect($tierOptions['options'])->not->toBeEmpty();
    });

    test('advancement selection state is tracked separately for first and second advancement', function () {
        $character = Character::factory()->create([
            'level' => 1,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        $component = Livewire::test(\App\Livewire\CharacterLevelUp::class, [
            'characterKey' => $character->character_key,
            'canEdit' => true,
        ]);

        // Initially no advancements selected
        $component->assertSet('first_advancement', null)
            ->assertSet('second_advancement', null);

        // Select first advancement (simulate AlpineJS selection)
        $component->set('first_advancement', 0);
        $component->assertSet('first_advancement', 0)
            ->assertSet('second_advancement', null);

        // Select second advancement
        $component->set('second_advancement', 1);
        $component->assertSet('first_advancement', 0)
            ->assertSet('second_advancement', 1);
    });

    test('validation requires both advancements to be selected for tier 2', function () {
        $character = Character::factory()->create([
            'level' => 1,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        $component = Livewire::test(\App\Livewire\CharacterLevelUp::class, [
            'characterKey' => $character->character_key,
            'canEdit' => true,
        ]);

        // Try to confirm with no selections
        $component->call('confirmLevelUp');

        // Should not level up
        $character->refresh();
        expect($character->level)->toBe(1);

        // Try with only first advancement
        $component->set('first_advancement', 0)
            ->call('confirmLevelUp');

        // Should still not level up
        $character->refresh();
        expect($character->level)->toBe(1);

        // Select both advancements and provide required choices
        $component->set('first_advancement', 0) // Trait bonus - requires trait selection
            ->set('second_advancement', 1) // Hit Point - no choices required
            ->set('advancement_choices.0.traits', ['agility', 'strength']) // Required for trait advancement
            ->call('confirmLevelUp');

        // Should now level up
        $character->refresh();
        expect($character->level)->toBe(2);
    });

    test('complete level up process creates all expected database records', function () {
        $character = Character::factory()->create([
            'level' => 1,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        $component = Livewire::test(\App\Livewire\CharacterLevelUp::class, [
            'characterKey' => $character->character_key,
            'canEdit' => true,
        ]);

        // Add tier experience
        $component->set('new_experience_name', 'Combat Training')
            ->set('new_experience_description', 'Advanced fighting techniques')
            ->call('addTierExperience');

        // Select advancements and provide required choices
        $component->set('first_advancement', 0) // Trait bonus
            ->set('second_advancement', 1) // Hit Point
            ->set('advancement_choices.0.traits', ['agility', 'strength']) // Required for trait advancement
            ->call('confirmLevelUp');

        // Verify character leveled up
        $character->refresh();
        expect($character->level)->toBe(2);

        // Verify tier experience was created
        $experiences = $character->experiences;
        expect($experiences)->toHaveCount(1);
        expect($experiences->first()->experience_name)->toBe('Combat Training');

        // Verify proficiency was increased (tier achievement applied directly)
        expect($character->proficiency)->toBe(2);

        // Verify regular advancements were created
        $regularAdvancements = CharacterAdvancement::where([
            'character_id' => $character->id,
        ])->where('advancement_number', '>', 0)->get();

        expect($regularAdvancements)->toHaveCount(2);
        expect($regularAdvancements->pluck('advancement_number')->sort()->values()->toArray())->toEqual([1, 2]);
    });

});

describe('Level Up State Management Tests', function () {

    test('advancement choices are preserved across component state', function () {
        $character = Character::factory()->create([
            'level' => 1,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        $component = Livewire::test(\App\Livewire\CharacterLevelUp::class, [
            'characterKey' => $character->character_key,
            'canEdit' => true,
        ]);

        // Add complex advancement choice data
        $component->set('advancement_choices.0.trait_selection', ['agility', 'strength']);
        $component->set('advancement_choices.1.experience_bonus', ['Combat Training', 'Survival']);

        // Verify data is preserved
        $choices = $component->get('advancement_choices');
        expect($choices[0]['trait_selection'])->toBe(['agility', 'strength']);
        expect($choices[1]['experience_bonus'])->toBe(['Combat Training', 'Survival']);
    });

    test('tier experience state persists correctly', function () {
        $character = Character::factory()->create([
            'level' => 1,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        $component = Livewire::test(\App\Livewire\CharacterLevelUp::class, [
            'characterKey' => $character->character_key,
            'canEdit' => true,
        ]);

        // Add experience
        $component->set('new_experience_name', 'Test Experience')
            ->set('new_experience_description', 'Test Description')
            ->call('addTierExperience');

        // Verify experience is stored in advancement_choices
        $tierExp = $component->get('advancement_choices.tier_experience');
        expect($tierExp['name'])->toBe('Test Experience');
        expect($tierExp['description'])->toBe('Test Description');
        expect($tierExp['modifier'])->toBe(2);

        // Remove experience
        $component->call('removeTierExperience');

        // Verify experience is removed
        expect($component->get('advancement_choices')['tier_experience'] ?? null)->toBeNull();
    });

    test('current tier is calculated correctly for different character levels', function () {
        // Test level 1 -> tier 2
        $character1 = Character::factory()->create(['level' => 1]);
        $component1 = Livewire::test(\App\Livewire\CharacterLevelUp::class, [
            'characterKey' => $character1->character_key,
            'canEdit' => true,
        ]);
        expect($component1->get('current_tier'))->toBe(2);

        // Test level 4 -> tier 3
        $character2 = Character::factory()->create(['level' => 4]);
        $component2 = Livewire::test(\App\Livewire\CharacterLevelUp::class, [
            'characterKey' => $character2->character_key,
            'canEdit' => true,
        ]);
        expect($component2->get('current_tier'))->toBe(3);

        // Test level 7 -> tier 4
        $character3 = Character::factory()->create(['level' => 7]);
        $component3 = Livewire::test(\App\Livewire\CharacterLevelUp::class, [
            'characterKey' => $character3->character_key,
            'canEdit' => true,
        ]);
        expect($component3->get('current_tier'))->toBe(4);
    });

});

describe('Integration with Character Viewer Tests', function () {

    test('character level up eligibility is calculated correctly', function () {
        // Test character that can level up using repository directly
        $character1 = Character::factory()->create(['level' => 1, 'is_public' => true]);
        $repository = new \Domain\Character\Repositories\CharacterAdvancementRepository;

        expect($repository->canLevelUp($character1))->toBeTrue();

        // Test character with filled advancement slots for next level
        $character2 = Character::factory()->create(['level' => 2, 'is_public' => true]);

        // Fill both advancement slots for the NEXT level (3)
        // This simulates a character who has already prepared their next level-up
        CharacterAdvancement::factory()->create([
            'character_id' => $character2->id,
            'tier' => 2,
            'level' => 3,
            'advancement_number' => 1,
        ]);
        CharacterAdvancement::factory()->create([
            'character_id' => $character2->id,
            'tier' => 2,
            'level' => 3,
            'advancement_number' => 2,
        ]);

        expect($repository->canLevelUp($character2))->toBeFalse();
    });

    test('advancement status is calculated correctly', function () {
        $character = Character::factory()->create(['level' => 2, 'is_public' => true]);
        $repository = new \Domain\Character\Repositories\CharacterAdvancementRepository;

        // Add one advancement for the next level (3)
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 2,
            'level' => 3,
            'advancement_number' => 1,
        ]);

        // Test available slots directly for next level (3)
        $availableSlots = $repository->getAvailableSlots($character->id, 3);
        expect($availableSlots)->toEqual([2]); // Only slot 2 should be available

        $canLevelUp = $repository->canLevelUp($character);
        expect($canLevelUp)->toBeTrue(); // Can still level up with one slot available
    });

    test('computed stats include advancement bonuses', function () {
        $character = Character::factory()->create([
            'level' => 3,
            'class' => 'warrior',
            'is_public' => true,
        ]);
        $repository = new \Domain\Character\Repositories\CharacterAdvancementRepository;

        // Add some advancements with explicit tier and advancement numbers
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 1,
            'level' => 1,
            'advancement_number' => 1,
            'advancement_type' => 'hit_point',
            'advancement_data' => ['bonus' => 2],
        ]);

        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 1,
            'level' => 1,
            'advancement_number' => 2,
            'advancement_type' => 'evasion',
            'advancement_data' => ['bonus' => 1],
        ]);

        // Test repository calculations directly
        $hitPointBonus = $repository->getHitPointBonus($character->id);
        $evasionBonus = $repository->getEvasionBonus($character->id);

        expect($hitPointBonus)->toBe(2);
        expect($evasionBonus)->toBe(1);
    });

});
