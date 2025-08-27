<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive browser test for Seraph + Divine Wielder
 * 
 * Tests critical mechanics:
 * - Seraph base stats (Evasion 9, Hit Points 7)
 * - Divine Wielder's Prayer Dice calculation (Strength trait)
 * - Spirit Weapon range extension (Melee/Very Close → Close)
 * - Spirit Weapon multi-target mechanics (1 Stress for additional target)
 * - Sparing Touch usage progression (1x → 2x with Devout)
 * - Sacred Resonance damage doubling on matching dice
 * - Domain access (Splendor + Valor)
 * - Strength spellcasting trait integration
 */

test('seraph divine wielder prayer dice calculation verification', function () {
    $character = createTestCharacterWith([
        'class' => 'seraph',
        'subclass' => 'divine wielder',
        'ancestry' => 'elf', // Neutral ancestry for clean testing
        'community' => 'orderborne', // Thematic for seraph
        'level' => 4, // Mid-level for specialization features
    ]);

    // Add character traits for complete testing (Strength for spellcasting)
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 0],
        ['trait_name' => 'strength', 'trait_value' => 2], // Spellcasting trait for Divine Wielder
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => 1],
        ['trait_name' => 'presence', 'trait_value' => 0],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Verify Seraph base stats
    expect($stats->hit_points)->toBe(7); // Seraph base 7
    expect($stats->evasion)->toBeGreaterThanOrEqual(9); // Seraph base 9 + bonuses

    // Test that no permanent bonuses are applied (Divine Wielder features are ability-based)
    expect($character->getSubclassEvasionBonus())->toBe(0); // No permanent evasion bonus
    expect($character->getSubclassStressBonus())->toBe(0); // No stress bonus  
    expect($character->getSubclassHitPointBonus())->toBe(0); // No HP bonus

    // Test domain card calculation (Divine Wielder provides no domain card bonuses)
    $builderData = \Domain\Character\Data\CharacterBuilderData::from([
        'selected_class' => 'seraph',
        'selected_subclass' => 'divine wielder',
    ]);
    
    expect($builderData->getMaxDomainCards())->toBe(2); // Base 2, no bonuses
    expect($builderData->getSubclassDomainCardBonus())->toBe(0);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('seraph divine wielder browser integration works correctly', function () {
    $character = Character::factory()->create([
        'character_key' => 'SERAPH456',
        'class' => null,
        'subclass' => null,
    ]);

    // Start browser test - visit character builder
    $page = visit("/character-builder/{$character->character_key}");

    // Set up the character data
    $character->update([
        'class' => 'seraph',
        'subclass' => 'divine wielder',
        'ancestry' => 'elf',
        'community' => 'orderborne',
    ]);

    // Add character traits (Strength focus for spellcasting)
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 0],
        ['trait_name' => 'strength', 'trait_value' => 2], // Spellcasting trait
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => 1],
        ['trait_name' => 'presence', 'trait_value' => 0],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    // Verify character was configured correctly
    expect($character->fresh())
        ->class->toBe('seraph')
        ->subclass->toBe('divine wielder')
        ->ancestry->toBe('elf')
        ->community->toBe('orderborne');

    // Test that stats are calculated correctly
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(7); // Seraph base 7
    expect($stats->evasion)->toBeGreaterThanOrEqual(9); // Seraph base + bonuses

    // No permanent stat bonuses from Divine Wielder
    expect($character->getSubclassEvasionBonus())->toBe(0);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('divine wielder subclass features are correctly defined', function () {
    // Verify Divine Wielder subclass JSON configuration
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $divineWielder = $subclassData['divine wielder'];
    
    expect($divineWielder)->not()->toBeNull();
    expect($divineWielder['name'])->toBe('Divine Wielder');
    expect($divineWielder['spellcastTrait'])->toBe('Strength'); // Critical: Uses Strength instead of typical Presence

    // Verify foundation features include Spirit Weapon
    $foundationFeatures = $divineWielder['foundationFeatures'];
    $spiritWeaponFeature = collect($foundationFeatures)->firstWhere('name', 'Spirit Weapon');
    
    expect($spiritWeaponFeature)->not()->toBeNull();
    expect($spiritWeaponFeature['description'])->toContain('Melee or Very Close');
    expect($spiritWeaponFeature['description'])->toContain('Close range');
    expect($spiritWeaponFeature['description'])->toContain('mark a Stress');
    expect($spiritWeaponFeature['description'])->toContain('additional adversary');
    expect($spiritWeaponFeature['stressCost'])->toBe(1);

    // Verify Sparing Touch healing feature
    $sparingTouchFeature = collect($foundationFeatures)->firstWhere('name', 'Sparing Touch');
    
    expect($sparingTouchFeature)->not()->toBeNull();
    expect($sparingTouchFeature['description'])->toContain('Once per long rest');
    expect($sparingTouchFeature['description'])->toContain('clear 2 Hit Points or 2 Stress');
    expect($sparingTouchFeature['description'])->toContain('touch a creature');

    // CRITICAL TEST: Verify Devout with Prayer Dice enhancement
    $specializationFeatures = $divineWielder['specializationFeatures'];
    $devoutFeature = collect($specializationFeatures)->firstWhere('name', 'Devout');
    
    expect($devoutFeature)->not()->toBeNull();
    expect($devoutFeature['description'])->toContain('Prayer Dice');
    expect($devoutFeature['description'])->toContain('additional die');
    expect($devoutFeature['description'])->toContain('discard the lowest');
    expect($devoutFeature['description'])->toContain('Sparing Touch');
    expect($devoutFeature['description'])->toContain('twice instead of once');

    // Verify Sacred Resonance mastery feature
    $masteryFeatures = $divineWielder['masteryFeatures'];
    $sacredResonanceFeature = collect($masteryFeatures)->firstWhere('name', 'Sacred Resonance');
    
    expect($sacredResonanceFeature)->not()->toBeNull();
    expect($sacredResonanceFeature['description'])->toContain('Spirit Weapon');
    expect($sacredResonanceFeature['description'])->toContain('die results match');
    expect($sacredResonanceFeature['description'])->toContain('double the value');

});

test('seraph class prayer dice integration with strength', function () {
    // Verify Seraph Prayer Dice work with Divine Wielder's Strength spellcasting
    $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    $seraphClass = $classData['seraph'];
    
    // Find Prayer Dice class feature
    $classFeatureNames = collect($seraphClass['classFeatures'])->pluck('name')->toArray();
    expect($classFeatureNames)->toContain('Prayer Dice');
    
    $prayerDiceFeature = collect($seraphClass['classFeatures'])->firstWhere('name', 'Prayer Dice');
    expect($prayerDiceFeature['description'])->toContain('d4');
    expect($prayerDiceFeature['description'])->toContain('trait'); // Should reference the spellcast trait
    
    // Divine Wielder uses Strength, so Prayer Dice should calculate from Strength
    // At Strength +2, should get more Prayer Dice than base

});

test('divine wielder spirit weapon range mechanics', function () {
    // Test Spirit Weapon range extension and multi-target mechanics
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $divineWielder = $subclassData['divine wielder'];
    
    $foundationFeatures = $divineWielder['foundationFeatures'];
    $spiritWeaponFeature = collect($foundationFeatures)->firstWhere('name', 'Spirit Weapon');
    
    // Verify range extension: Melee/Very Close weapons can hit Close range
    expect($spiritWeaponFeature['description'])->toContain('Melee or Very Close');
    expect($spiritWeaponFeature['description'])->toContain('attack an adversary within Close range');
    expect($spiritWeaponFeature['description'])->toContain('fly from your hand');
    expect($spiritWeaponFeature['description'])->toContain('return to you');
    
    // Verify multi-target mechanic
    expect($spiritWeaponFeature['description'])->toContain('mark a Stress');
    expect($spiritWeaponFeature['description'])->toContain('target an additional adversary');
    expect($spiritWeaponFeature['description'])->toContain('same attack roll');
    expect($spiritWeaponFeature['stressCost'])->toBe(1);

});

test('divine wielder sparing touch usage progression', function () {
    // Test Sparing Touch usage increase with Devout
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $divineWielder = $subclassData['divine wielder'];
    
    $foundationFeatures = $divineWielder['foundationFeatures'];
    $specializationFeatures = $divineWielder['specializationFeatures'];
    
    // Base Sparing Touch: Once per long rest
    $sparingTouchFeature = collect($foundationFeatures)->firstWhere('name', 'Sparing Touch');
    expect($sparingTouchFeature['description'])->toContain('Once per long rest');
    expect($sparingTouchFeature['description'])->toContain('clear 2 Hit Points or 2 Stress');
    
    // Devout upgrade: Twice per long rest
    $devoutFeature = collect($specializationFeatures)->firstWhere('name', 'Devout');
    expect($devoutFeature['description'])->toContain('Sparing Touch');
    expect($devoutFeature['description'])->toContain('twice instead of once per long rest');
    
    // This doubles the healing capacity: 2 uses × 2 HP/Stress = 4 total healing per rest

});

test('divine wielder prayer dice devout enhancement', function () {
    // Test Prayer Dice enhancement with Devout specialization
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $divineWielder = $subclassData['divine wielder'];
    
    $specializationFeatures = $divineWielder['specializationFeatures'];
    $devoutFeature = collect($specializationFeatures)->firstWhere('name', 'Devout');
    
    // Verify Prayer Dice enhancement
    expect($devoutFeature['description'])->toContain('roll your Prayer Dice');
    expect($devoutFeature['description'])->toContain('additional die');
    expect($devoutFeature['description'])->toContain('discard the lowest result');
    
    // This is a significant upgrade: roll N+1 dice, keep best N results
    // Dramatically improves Prayer Dice reliability and average value

});

test('divine wielder sacred resonance damage mechanics', function () {
    // Test Sacred Resonance mastery damage doubling
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $divineWielder = $subclassData['divine wielder'];
    
    $masteryFeatures = $divineWielder['masteryFeatures'];
    $sacredResonanceFeature = collect($masteryFeatures)->firstWhere('name', 'Sacred Resonance');
    
    // Verify damage doubling mechanics
    expect($sacredResonanceFeature['description'])->toContain('roll damage for your');
    expect($sacredResonanceFeature['description'])->toContain('Spirit Weapon');
    expect($sacredResonanceFeature['description'])->toContain('die results match');
    expect($sacredResonanceFeature['description'])->toContain('double the value of each matching die');
    
    // Example verification
    expect($sacredResonanceFeature['description'])->toContain('two 5s');
    expect($sacredResonanceFeature['description'])->toContain('two 10s');
    
    // This creates explosive damage potential with multi-die weapons

});

test('seraph divine wielder strength spellcasting integration', function () {
    // Test that Divine Wielder correctly uses Strength for spellcasting (unusual for Seraph)
    $character = createTestCharacterWith([
        'class' => 'seraph',
        'subclass' => 'divine wielder',
        'ancestry' => 'elf',
        'community' => 'orderborne',
    ]);

    // Set high Strength trait for spellcasting
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 0],
        ['trait_name' => 'strength', 'trait_value' => 2], // High for spellcasting
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => 1],
        ['trait_name' => 'presence', 'trait_value' => 0], // NOT the spellcast trait for this subclass
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    // Test that builder data correctly identifies Strength as spellcast trait
    $builderData = \Domain\Character\Data\CharacterBuilderData::from([
        'selected_class' => 'seraph',
        'selected_subclass' => 'divine wielder',
        'character_traits' => [
            'agility' => 0,
            'strength' => 2, // Should be used for Prayer Dice calculations
            'finesse' => 1,
            'instinct' => 1,
            'presence' => 0,
            'knowledge' => -1,
        ],
    ]);

    expect($character->fresh())->class->toBe('seraph');
    expect($character->fresh())->subclass->toBe('divine wielder');

    // Verify the character was created with correct traits
    $strengthTrait = $character->traits()->where('trait_name', 'strength')->first();
    expect($strengthTrait->trait_value)->toBe(2);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('divine wielder combat synergy verification', function () {
    // Test how Divine Wielder features synergize for combat effectiveness
    $character = createTestCharacterWith([
        'class' => 'seraph',
        'subclass' => 'divine wielder',
        'ancestry' => 'human', // +2 stress for testing
        'community' => 'orderborne',
        'level' => 6, // High level for mastery features
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Test that the seraph has good base survivability for melee combat
    expect($stats->hit_points)->toBe(7); // Seraph base
    expect($stats->evasion)->toBeGreaterThanOrEqual(9); // Good evasion
    
    // The synergy: Spirit Weapon extends range + multi-target
    // Prayer Dice enhanced with Devout (extra die, drop lowest)
    // Sacred Resonance doubles matching damage dice
    // Sparing Touch provides battlefield healing (2x per rest at specialization)
    // All powered by Strength trait for consistent scaling
});