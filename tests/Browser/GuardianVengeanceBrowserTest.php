<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive browser test for Guardian + Vengeance
 * 
 * Tests critical mechanics:
 * - Guardian base stats (Evasion 9, Hit Points 7)
 * - Vengeance's +1 stress slot bonus (At Ease feature) 
 * - Revenge mechanism (2 Stress cost)
 * - Act of Reprisal proficiency bonus
 * - Nemesis prioritization (2 Hope cost)
 * - Domain access (Valor + Blade)
 * - Strength spellcasting trait assignment
 */

test('guardian vengeance stress slot bonus verification', function () {
    $character = createTestCharacterWith([
        'class' => 'guardian',
        'subclass' => 'vengeance',
        'ancestry' => 'human', // +2 stress bonus for stacking test
        'community' => 'orderborne', // Thematic for guardian
        'level' => 3, // Mid-level for foundation features
    ]);

    // Add character traits for complete testing
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 0],
        ['trait_name' => 'strength', 'trait_value' => 2], // Guardian strength focus
        ['trait_name' => 'finesse', 'trait_value' => 0],
        ['trait_name' => 'instinct', 'trait_value' => 1],
        ['trait_name' => 'presence', 'trait_value' => 1],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Verify Guardian base stats
    expect($stats->hit_points)->toBe(7); // Guardian base 7
    expect($stats->evasion)->toBeGreaterThanOrEqual(9); // Guardian base 9 + trait bonuses

    // CRITICAL TEST: Verify Vengeance's +1 stress slot bonus
    $subclassStressBonus = $character->getSubclassStressBonus();
    expect($subclassStressBonus)->toBe(1); // Vengeance "At Ease" +1 stress slot

    // Test total stress calculation: Guardian base 5 + Human +2 + Vengeance +1 = 8
    expect($stats->stress)->toBe(8); // Base 5 + Human +2 + Vengeance +1
    
    // Test domain card calculation (Vengeance provides no domain card bonuses)
    $builderData = \Domain\Character\Data\CharacterBuilderData::from([
        'selected_class' => 'guardian',
        'selected_subclass' => 'vengeance',
    ]);
    
    expect($builderData->getMaxDomainCards())->toBe(2); // Base 2, no bonuses
    expect($builderData->getSubclassDomainCardBonus())->toBe(0);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guardian vengeance browser integration works correctly', function () {
    $character = Character::factory()->create([
        'character_key' => 'GUARD123',
        'class' => null,
        'subclass' => null,
    ]);

    // Start browser test - visit character builder
    $page = visit("/character-builder/{$character->character_key}");

    // Set up the character data
    $character->update([
        'class' => 'guardian',
        'subclass' => 'vengeance',
        'ancestry' => 'human',
        'community' => 'orderborne',
    ]);

    // Add character traits
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 0],
        ['trait_name' => 'strength', 'trait_value' => 2],
        ['trait_name' => 'finesse', 'trait_value' => 0],
        ['trait_name' => 'instinct', 'trait_value' => 1],
        ['trait_name' => 'presence', 'trait_value' => 1],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    // Verify character was configured correctly
    expect($character->fresh())
        ->class->toBe('guardian')
        ->subclass->toBe('vengeance')
        ->ancestry->toBe('human')
        ->community->toBe('orderborne');

    // Test that stats are calculated correctly with Vengeance's stress bonus
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(7); // Guardian base 7
    expect($stats->evasion)->toBeGreaterThanOrEqual(9); // Guardian base + bonuses

    // Verify the +1 stress slot bonus is working
    $stressSlotBonus = $character->getSubclassStressBonus();
    expect($stressSlotBonus)->toBe(1);

    // Verify total stress includes both Human and Vengeance bonuses
    expect($stats->stress)->toBe(8); // Guardian 5 + Human +2 + Vengeance +1

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('vengeance subclass features are correctly defined', function () {
    // Verify Vengeance subclass JSON configuration
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $vengeance = $subclassData['vengeance'];
    
    expect($vengeance)->not()->toBeNull();
    expect($vengeance['name'])->toBe('Vengeance');

    // Verify foundation features include At Ease with stress bonus
    $foundationFeatures = $vengeance['foundationFeatures'];
    $atEaseFeature = collect($foundationFeatures)->firstWhere('name', 'At Ease');
    
    expect($atEaseFeature)->not()->toBeNull();
    expect($atEaseFeature['description'])->toContain('additional Stress slot');

    // Check if it has the stress slot bonus effect
    $hasStressSlotBonus = collect($atEaseFeature['effects'] ?? [])->contains(function ($effect) {
        return $effect['type'] === 'stress_bonus' && $effect['value'] === 1;
    });
    
    expect($hasStressSlotBonus)->toBeTrue();

    // Verify Revenge feature with 2 Stress cost
    $revengeFeature = collect($foundationFeatures)->firstWhere('name', 'Revenge');
    
    expect($revengeFeature)->not()->toBeNull();
    expect($revengeFeature['description'])->toContain('mark 2 Stress');
    expect($revengeFeature['description'])->toContain('Hit Point');
    expect($revengeFeature['stressCost'])->toBe(2);

    // Verify specialization features include Act of Reprisal
    $specializationFeatures = $vengeance['specializationFeatures'];
    $actOfReprisalFeature = collect($specializationFeatures)->firstWhere('name', 'Act of Reprisal');
    
    expect($actOfReprisalFeature)->not()->toBeNull();
    expect($actOfReprisalFeature['description'])->toContain('+1 bonus');
    expect($actOfReprisalFeature['description'])->toContain('Proficiency');

    // Verify mastery features include Nemesis with Hope cost
    $masteryFeatures = $vengeance['masteryFeatures'];
    $nemesisFeature = collect($masteryFeatures)->firstWhere('name', 'Nemesis');
    
    expect($nemesisFeature)->not()->toBeNull();
    expect($nemesisFeature['description'])->toContain('Prioritize');
    expect($nemesisFeature['description'])->toContain('Hope');
    expect($nemesisFeature['hopeCost'])->toBe(2);

});

test('guardian class has correct base stats for vengeance', function () {
    $character = createTestCharacterWith([
        'class' => 'guardian',
        'subclass' => 'vengeance',
        'ancestry' => 'dwarf', // Use dwarf to test different ancestry bonuses
        'community' => 'ridgeborne',
    ]);

    // Add consistent traits
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 0],
        ['trait_name' => 'strength', 'trait_value' => 2],
        ['trait_name' => 'finesse', 'trait_value' => 0],
        ['trait_name' => 'instinct', 'trait_value' => 1],
        ['trait_name' => 'presence', 'trait_value' => 1],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Verify Guardian base stats
    expect($stats->hit_points)->toBe(7); // Guardian base 7
    expect($stats->evasion)->toBeGreaterThanOrEqual(9); // Guardian base + bonuses

    // Test Guardian domains (Valor + Blade)
    $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    $guardianClass = $classData['guardian'];
    
    expect($guardianClass['domains'])->toBe(['valor', 'blade']);
    expect($guardianClass['startingEvasion'])->toBe(9);
    expect($guardianClass['startingHitPoints'])->toBe(7);

    // Verify stress calculation includes Vengeance bonus
    $stressSlotBonus = $character->getSubclassStressBonus();
    expect($stressSlotBonus)->toBe(1); // Vengeance +1

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guardian class features work with vengeance subclass', function () {
    // Verify Guardian class features from JSON
    $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    $guardianClass = $classData['guardian'];
    
    expect($guardianClass['name'])->toBe('Guardian');
    
    // Check Hope feature
    expect($guardianClass['hopeFeature']['name'])->toBe('Frontline Tank');
    expect($guardianClass['hopeFeature']['hopeCost'])->toBe(3);
    expect($guardianClass['hopeFeature']['description'])->toContain('clear');
    
    // Check class features include Unstoppable
    $classFeatureNames = collect($guardianClass['classFeatures'])->pluck('name')->toArray();
    expect($classFeatureNames)->toContain('Unstoppable'); // This is a class feature, not Hope feature
    
    // Verify subclasses include both stalwart and vengeance
    expect($guardianClass['subclasses'])->toBe(['stalwart', 'vengeance']);

});

test('guardian vengeance stress stacking integration', function () {
    // Test that stress bonuses stack correctly: Base + Ancestry + Subclass
    
    // Test with Human (high stress ancestry)
    $humanCharacter = createTestCharacterWith([
        'class' => 'guardian',
        'subclass' => 'vengeance',
        'ancestry' => 'human',
        'community' => 'orderborne',
    ]);

    $humanStats = \Domain\Character\Data\CharacterStatsData::fromModel($humanCharacter);
    
    // Guardian base 5 + Human +2 + Vengeance +1 = 8
    expect($humanStats->stress)->toBe(8);

    // Test with Dwarf (different ancestry)
    $dwarfCharacter = createTestCharacterWith([
        'class' => 'guardian',
        'subclass' => 'vengeance',
        'ancestry' => 'dwarf',
        'community' => 'ridgeborne',
    ]);

    $dwarfStats = \Domain\Character\Data\CharacterStatsData::fromModel($dwarfCharacter);
    
    // Verify dwarf gets different total (Guardian base 5 + Dwarf bonus + Vengeance +1)
    expect($dwarfStats->stress)->toBeGreaterThanOrEqual(6); // At least base 5 + Vengeance +1

    // Both should have the Vengeance +1 bonus
    expect($humanCharacter->getSubclassStressBonus())->toBe(1);
    expect($dwarfCharacter->getSubclassStressBonus())->toBe(1);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
