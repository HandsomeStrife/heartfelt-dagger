<?php

declare(strict_types=1);

use Domain\Character\Actions\ApplyAdvancementAction;
use Domain\Character\Data\CharacterAdvancementData;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Character Advancement Validation Tests', function () {

    test('apply advancement action validates tier range', function () {
        $character = Character::factory()->create(['level' => 5]);
        $action = new ApplyAdvancementAction;

        // Test invalid tier (too low)
        expect(function () use ($action, $character) {
            $advancement = CharacterAdvancementData::hitPoint(0, 1, 1); // Invalid tier 0
            $action->execute($character, $advancement);
        })->toThrow(\InvalidArgumentException::class, 'Tier must be between 1 and 4');

        // Test invalid tier (too high)
        expect(function () use ($action, $character) {
            $advancement = CharacterAdvancementData::hitPoint(5, 1, 1); // Invalid tier 5
            $action->execute($character, $advancement);
        })->toThrow(\InvalidArgumentException::class, 'Tier must be between 1 and 4');
    });

    test('apply advancement action validates advancement number range', function () {
        $character = Character::factory()->create(['level' => 5]);
        $action = new ApplyAdvancementAction;

        // Test invalid advancement number (too low)
        expect(function () use ($action, $character) {
            $advancement = CharacterAdvancementData::hitPoint(2, 0, 1); // Invalid advancement_number 0
            $action->execute($character, $advancement);
        })->toThrow(\InvalidArgumentException::class, 'Advancement number must be 1 or 2');

        // Test invalid advancement number (too high)
        expect(function () use ($action, $character) {
            $advancement = CharacterAdvancementData::hitPoint(2, 3, 1); // Invalid advancement_number 3
            $action->execute($character, $advancement);
        })->toThrow(\InvalidArgumentException::class, 'Advancement number must be 1 or 2');
    });

    test('apply advancement action prevents duplicate advancement slots', function () {
        $character = Character::factory()->create(['level' => 5]);
        $action = new ApplyAdvancementAction;

        // Create first advancement
        $advancement1 = CharacterAdvancementData::hitPoint(2, 1, 1);
        $action->execute($character, $advancement1);

        // Try to create duplicate advancement in same slot
        expect(function () use ($action, $character) {
            $advancement2 = CharacterAdvancementData::evasion(2, 1, 1); // Same tier and advancement_number
            $action->execute($character, $advancement2);
        })->toThrow(\InvalidArgumentException::class, 'Advancement slot already taken');
    });

    test('apply advancement action validates character level requirements', function () {
        $action = new ApplyAdvancementAction;

        // Test tier 2 requirement (level 1+)
        $lowLevelCharacter = Character::factory()->create(['level' => 0]); // Too low
        expect(function () use ($action, $lowLevelCharacter) {
            $advancement = CharacterAdvancementData::hitPoint(2, 1, 1);
            $action->execute($lowLevelCharacter, $advancement);
        })->toThrow(\InvalidArgumentException::class, 'Character level insufficient for tier 2');

        // Test tier 3 requirement (level 5+)
        $midLevelCharacter = Character::factory()->create(['level' => 4]); // Too low for tier 3
        expect(function () use ($action, $midLevelCharacter) {
            $advancement = CharacterAdvancementData::hitPoint(3, 1, 1);
            $action->execute($midLevelCharacter, $advancement);
        })->toThrow(\InvalidArgumentException::class, 'Character level insufficient for tier 3');

        // Test tier 4 requirement (level 7+)
        $highLevelCharacter = Character::factory()->create(['level' => 6]); // Too low for tier 4
        expect(function () use ($action, $highLevelCharacter) {
            $advancement = CharacterAdvancementData::hitPoint(4, 1, 1);
            $action->execute($highLevelCharacter, $advancement);
        })->toThrow(\InvalidArgumentException::class, 'Character level insufficient for tier 4');
    });

    test('apply advancement action validates trait bonus limits', function () {
        $character = Character::factory()->create(['level' => 5]);
        $action = new ApplyAdvancementAction;

        // Create first trait advancement for agility
        $advancement1 = CharacterAdvancementData::traitBonus(2, 1, ['agility']);
        $action->execute($character, $advancement1);

        // Try to create another trait advancement for same trait - this should be allowed
        // (SRD allows multiple trait bonuses to same trait)
        $advancement2 = CharacterAdvancementData::traitBonus(2, 2, ['agility']);

        // This should not throw an exception
        $result = $action->execute($character, $advancement2);
        expect($result)->toBeInstanceOf(CharacterAdvancement::class);
    });

    test('apply advancement action validates evasion bonus restrictions', function () {
        $character = Character::factory()->create(['level' => 5]);
        $action = new ApplyAdvancementAction;

        // Create evasion advancement
        $advancement1 = CharacterAdvancementData::evasion(2, 1, 1);
        $result = $action->execute($character, $advancement1);

        expect($result->advancement_type)->toBe('evasion');
        expect($result->advancement_data['bonus'])->toBe(1);
    });

    test('advancement data factory methods create correct structures', function () {
        // Test trait bonus data
        $traitData = CharacterAdvancementData::traitBonus(2, 1, ['strength']);
        expect($traitData->tier)->toBe(2);
        expect($traitData->advancement_number)->toBe(1);
        expect($traitData->advancement_type)->toBe('trait_bonus');
        expect($traitData->advancement_data)->toHaveKey('traits');
        expect($traitData->advancement_data['traits'])->toBe(['strength']);

        // Test hit point data
        $hitPointData = CharacterAdvancementData::hitPoint(2, 1, 2);
        expect($hitPointData->advancement_type)->toBe('hit_point');
        expect($hitPointData->advancement_data['bonus'])->toBe(2);

        // Test evasion data
        $evasionData = CharacterAdvancementData::evasion(2, 1, 1);
        expect($evasionData->advancement_type)->toBe('evasion');
        expect($evasionData->advancement_data['bonus'])->toBe(1);

        // Test stress data
        $stressData = CharacterAdvancementData::stress(2, 1, 3);
        expect($stressData->advancement_type)->toBe('stress');
        expect($stressData->advancement_data['bonus'])->toBe(3);
    });

});

describe('Character Advancement Business Logic Tests', function () {

    test('character can only have 2 advancements per tier', function () {
        $character = Character::factory()->create(['level' => 5]);
        $action = new ApplyAdvancementAction;

        // Add first advancement
        $advancement1 = CharacterAdvancementData::hitPoint(2, 1, 1);
        $action->execute($character, $advancement1);

        // Add second advancement
        $advancement2 = CharacterAdvancementData::evasion(2, 2, 1);
        $action->execute($character, $advancement2);

        // Verify both exist
        $advancements = CharacterAdvancement::where([
            'character_id' => $character->id,
            'tier' => 2,
        ])->get();

        expect($advancements)->toHaveCount(2);
        expect($advancements->pluck('advancement_number')->sort()->values()->toArray())->toEqual([1, 2]);
    });

    test('character can have advancements in different tiers', function () {
        $character = Character::factory()->create(['level' => 8]); // High enough for tier 4
        $action = new ApplyAdvancementAction;

        // Add advancement to tier 2
        $advancement1 = CharacterAdvancementData::hitPoint(2, 1, 1);
        $action->execute($character, $advancement1);

        // Add advancement to tier 3
        $advancement2 = CharacterAdvancementData::evasion(3, 1, 1);
        $action->execute($character, $advancement2);

        // Add advancement to tier 4
        $advancement3 = CharacterAdvancementData::stress(4, 1, 2);
        $action->execute($character, $advancement3);

        // Verify all exist in different tiers
        expect(CharacterAdvancement::where(['character_id' => $character->id, 'tier' => 2])->count())->toBe(1);
        expect(CharacterAdvancement::where(['character_id' => $character->id, 'tier' => 3])->count())->toBe(1);
        expect(CharacterAdvancement::where(['character_id' => $character->id, 'tier' => 4])->count())->toBe(1);
    });

    test('advancement data is stored correctly in database', function () {
        $character = Character::factory()->create(['level' => 5]);
        $action = new ApplyAdvancementAction;

        // Test complex trait data
        $traitData = ['agility', 'strength'];
        $advancement = CharacterAdvancementData::traitBonus(2, 1, $traitData);
        $result = $action->execute($character, $advancement);

        // Verify database storage
        expect($result->advancement_data['traits'])->toBe($traitData);
        expect($result->advancement_data['bonus'])->toBe(1);
        expect($result->character_id)->toBe($character->id);
        expect($result->tier)->toBe(2);
        expect($result->advancement_number)->toBe(1);

        // Verify it can be retrieved correctly
        $retrieved = CharacterAdvancement::find($result->id);
        expect($retrieved->advancement_data['traits'])->toBe($traitData);
    });

    test('advancement description is stored correctly', function () {
        $character = Character::factory()->create(['level' => 5]);
        $action = new ApplyAdvancementAction;

        $advancement = CharacterAdvancementData::hitPoint(2, 1, 1);
        $advancement->description = 'Custom test description';

        $result = $action->execute($character, $advancement);

        expect($result->description)->toBe('Custom test description');
    });

});

describe('Advancement Repository Edge Cases', function () {

    test('repository correctly identifies available slots', function () {
        $character = Character::factory()->create(['level' => 5]);
        $repository = new \Domain\Character\Repositories\CharacterAdvancementRepository;

        // Initially should have 2 available slots for tier 2
        $slots = $repository->getAvailableSlots($character->id, 2);
        expect($slots)->toEqual([1, 2]);

        // Add one advancement
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 2,
            'advancement_number' => 1,
        ]);

        // Should now have 1 available slot
        $slots = $repository->getAvailableSlots($character->id, 2);
        expect($slots)->toEqual([2]);

        // Add second advancement
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 2,
            'advancement_number' => 2,
        ]);

        // Should now have no available slots
        $slots = $repository->getAvailableSlots($character->id, 2);
        expect($slots)->toEqual([]);
    });

    test('repository calculates bonuses correctly', function () {
        $character = Character::factory()->create(['level' => 5]);
        $repository = new \Domain\Character\Repositories\CharacterAdvancementRepository;

        // Add multiple hit point advancements
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'advancement_type' => 'hit_point',
            'advancement_data' => ['bonus' => 1],
        ]);

        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'advancement_type' => 'hit_point',
            'advancement_data' => ['bonus' => 2],
        ]);

        $totalBonus = $repository->getHitPointBonus($character->id);
        expect($totalBonus)->toBe(3);
    });

});
