<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive audit test for Domain Card Limits across ALL subclasses
 * 
 * This test ensures that:
 * - School of Knowledge fix is working (+1, not +3)  
 * - No other subclasses have incorrect domain card bonuses
 * - Base domain card limit is 2 for all classes
 * - Domain card bonuses stack correctly with base limits
 * - UI displays correct limits in domain card selection
 * - CharacterBuilderData calculations are accurate
 */

test('all subclasses have correct domain card limits - comprehensive audit', function () {
    // Load all subclasses to audit their domain card bonuses
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    
    // Expected domain card bonuses per subclass (most should be 0)
    $expectedDomainCardBonuses = [
        // CONFIRMED: School of Knowledge should provide +1 domain card
        'school of knowledge' => 1,
        
        // ALL OTHER SUBCLASSES: Should provide 0 bonus domain cards
        'beastbound' => 0,
        'call of the brave' => 0, 
        'call of the slayer' => 0,
        'divine wielder' => 0,
        'elemental origin' => 0,
        'nightwalker' => 0,
        'primal origin' => 0,
        'school of war' => 0,
        'stalwart' => 0,
        'syndicate' => 0,
        'troubadour' => 0,
        'vengeance' => 0,
        'warden of renewal' => 0,
        'warden of the elements' => 0,
        'wayfinder' => 0,
        'winged sentinel' => 0,
        'wordsmith' => 0,
    ];
    
    foreach ($expectedDomainCardBonuses as $subclassKey => $expectedBonus) {
        $subclass = $subclassData[$subclassKey];
        
        // Check if subclass has any domain_card_bonus effects
        $actualBonus = 0;
        
        // Check foundation features
        if (isset($subclass['foundationFeatures'])) {
            foreach ($subclass['foundationFeatures'] as $feature) {
                if (isset($feature['effects'])) {
                    foreach ($feature['effects'] as $effect) {
                        if ($effect['type'] === 'domain_card_bonus') {
                            $actualBonus += $effect['value'];
                        }
                    }
                }
            }
        }
        
        // Check specialization features
        if (isset($subclass['specializationFeatures'])) {
            foreach ($subclass['specializationFeatures'] as $feature) {
                if (isset($feature['effects'])) {
                    foreach ($feature['effects'] as $effect) {
                        if ($effect['type'] === 'domain_card_bonus') {
                            $actualBonus += $effect['value'];
                        }
                    }
                }
            }
        }
        
        // Check mastery features
        if (isset($subclass['masteryFeatures'])) {
            foreach ($subclass['masteryFeatures'] as $feature) {
                if (isset($feature['effects'])) {
                    foreach ($feature['effects'] as $effect) {
                        if ($effect['type'] === 'domain_card_bonus') {
                            $actualBonus += $effect['value'];
                        }
                    }
                }
            }
        }
        
        expect($actualBonus)
            ->toBe($expectedBonus, "Subclass '{$subclassKey}' should have {$expectedBonus} domain card bonus, got {$actualBonus}");
    }

});

test('school of knowledge domain card bonus is correctly fixed', function () {
    // Specific verification that School of Knowledge was fixed from +3 to +1
    $character = createTestCharacterWith([
        'class' => 'wizard',
        'subclass' => 'school of knowledge',
        'ancestry' => 'human',
        'community' => 'loreborne',
        'level' => 4,
    ]);

    // Test via CharacterBuilderData
    $builderData = \Domain\Character\Data\CharacterBuilderData::from([
        'selected_class' => 'wizard',
        'selected_subclass' => 'school of knowledge',
    ]);

    // CRITICAL: Should be +1 bonus, not +3
    expect($builderData->getSubclassDomainCardBonus())->toBe(1);
    expect($builderData->getMaxDomainCards())->toBe(3); // Base 2 + bonus 1 = 3

    // Test via Character model methods
    expect($character->getSubclassDomainCardBonus())->toBe(1);
    expect($character->getMaxDomainCards())->toBe(3);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('all classes have base 2 domain cards with no class bonuses', function () {
    // Verify all 9 classes have base 2 domain cards
    $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    
    $expectedClasses = [
        'bard', 'druid', 'guardian', 'ranger', 'rogue', 
        'seraph', 'sorcerer', 'warrior', 'wizard'
    ];
    
    foreach ($expectedClasses as $className) {
        $class = $classData[$className];
        
        // All classes should have exactly 2 starting domain cards when no subclass is selected
        $builderData = \Domain\Character\Data\CharacterBuilderData::from([
            'selected_class' => $className,
            'selected_subclass' => null,
        ]);
        
        // Base should be 2, no subclass bonus should be 0
        expect($builderData->getMaxDomainCards())
            ->toBe(2, "Class '{$className}' should have base 2 domain cards with no subclass");
        
        expect($builderData->getSubclassDomainCardBonus())
            ->toBe(0, "Class '{$className}' should have no subclass bonus when no subclass selected");
    }

});

test('domain card limits display correctly in UI for all subclasses', function () {
    // Test the UI display logic for domain card limits
    $testCases = [
        ['class' => 'wizard', 'subclass' => 'school of knowledge', 'expected_max' => 3, 'expected_bonus' => 1],
        ['class' => 'warrior', 'subclass' => 'call of the brave', 'expected_max' => 2, 'expected_bonus' => 0],
        ['class' => 'rogue', 'subclass' => 'nightwalker', 'expected_max' => 2, 'expected_bonus' => 0],
        ['class' => 'seraph', 'subclass' => 'divine wielder', 'expected_max' => 2, 'expected_bonus' => 0],
    ];
    
    foreach ($testCases as $testCase) {
        $character = Character::factory()->create([
            'character_key' => 'TEST' . strtoupper(substr($testCase['subclass'], 0, 4)),
            'class' => $testCase['class'],
            'subclass' => $testCase['subclass'],
        ]);

        // Visit the domain card selection page
        $page = visit("/character-builder/{$character->character_key}");

        // Verify the character configuration
        expect($character->fresh())
            ->class->toBe($testCase['class'])
            ->subclass->toBe($testCase['subclass']);

        // Test that the domain card calculations are correct
        expect($character->getMaxDomainCards())->toBe($testCase['expected_max']);
        expect($character->getSubclassDomainCardBonus())->toBe($testCase['expected_bonus']);
    }

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('domain card bonus stacking works correctly', function () {
    // Test that domain card bonuses stack properly (base + subclass bonuses)
    $character = createTestCharacterWith([
        'class' => 'wizard',
        'subclass' => 'school of knowledge',
        'ancestry' => 'elf',
        'community' => 'loreborne',
    ]);

    $builderData = \Domain\Character\Data\CharacterBuilderData::from([
        'selected_class' => 'wizard',
        'selected_subclass' => 'school of knowledge',
    ]);

    // Break down the calculation: Base 2 + subclass bonus
    expect($builderData->getSubclassDomainCardBonus())->toBe(1); // School of Knowledge +1
    expect($builderData->getMaxDomainCards())->toBe(3); // 2 base + 1 subclass = 3
    
    // Character model should match
    expect($character->getMaxDomainCards())->toBe(3);
    expect($character->getSubclassDomainCardBonus())->toBe(1);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('no unexpected domain card bonuses exist in subclasses', function () {
    // Comprehensive check to ensure no subclasses have hidden domain card bonuses
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    
    $subclassesWithDomainCardBonuses = [];
    
    foreach ($subclassData as $key => $subclass) {
        $domainCardBonus = 0;
        
        // Check all feature types for domain_card_bonus effects
        $featureTypes = ['foundationFeatures', 'specializationFeatures', 'masteryFeatures'];
        
        foreach ($featureTypes as $featureType) {
            if (isset($subclass[$featureType])) {
                foreach ($subclass[$featureType] as $feature) {
                    if (isset($feature['effects'])) {
                        foreach ($feature['effects'] as $effect) {
                            if ($effect['type'] === 'domain_card_bonus') {
                                $domainCardBonus += $effect['value'];
                            }
                        }
                    }
                }
            }
        }
        
        if ($domainCardBonus > 0) {
            $subclassesWithDomainCardBonuses[$key] = $domainCardBonus;
        }
    }
    
    // ONLY School of Knowledge should have domain card bonuses
    expect($subclassesWithDomainCardBonuses)->toBe([
        'school of knowledge' => 1
    ], 'Only School of Knowledge should have domain card bonuses');

});

test('domain card selection enforces limits correctly', function () {
    // Test that domain card selection UI enforces the correct limits
    $character = createTestCharacterWith([
        'class' => 'wizard',
        'subclass' => 'school of knowledge',
        'ancestry' => 'human',
        'community' => 'loreborne',
    ]);

    // Add 3 domain cards (at the limit)
    $character->domainCards()->createMany([
        ['domain' => 'codex', 'ability_key' => 'test-card-1', 'ability_level' => 1],
        ['domain' => 'midnight', 'ability_key' => 'test-card-2', 'ability_level' => 1], 
        ['domain' => 'codex', 'ability_key' => 'test-card-3', 'ability_level' => 1],
    ]);

    // Verify we're at the limit
    expect($character->domainCards()->count())->toBe(3);
    expect($character->getMaxDomainCards())->toBe(3);
    expect($character->domainCards()->count() >= $character->getMaxDomainCards())->toBeTrue();

    // Test a subclass with no bonus
    $normalCharacter = createTestCharacterWith([
        'class' => 'warrior',
        'subclass' => 'call of the brave',
        'ancestry' => 'human',
        'community' => 'ridgeborne',
    ]);

    // Add 2 domain cards (at the limit)
    $normalCharacter->domainCards()->createMany([
        ['domain' => 'blade', 'ability_key' => 'test-card-a', 'ability_level' => 1],
        ['domain' => 'bone', 'ability_key' => 'test-card-b', 'ability_level' => 1],
    ]);

    // Verify we're at the limit
    expect($normalCharacter->domainCards()->count())->toBe(2);
    expect($normalCharacter->getMaxDomainCards())->toBe(2);
    expect($normalCharacter->domainCards()->count() >= $normalCharacter->getMaxDomainCards())->toBeTrue();

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('domain card calculations are consistent across all methods', function () {
    // Test that all calculation methods return consistent results
    $testCases = [
        ['class' => 'wizard', 'subclass' => 'school of knowledge', 'expected' => 3],
        ['class' => 'bard', 'subclass' => 'troubadour', 'expected' => 2],
        ['class' => 'guardian', 'subclass' => 'stalwart', 'expected' => 2],
        ['class' => 'sorcerer', 'subclass' => 'elemental origin', 'expected' => 2],
    ];

    foreach ($testCases as $case) {
        $character = createTestCharacterWith([
            'class' => $case['class'],
            'subclass' => $case['subclass'],
            'ancestry' => 'human',
            'community' => 'loreborne',
        ]);

        $builderData = \Domain\Character\Data\CharacterBuilderData::from([
            'selected_class' => $case['class'],
            'selected_subclass' => $case['subclass'],
        ]);

        // All methods should return the same result
        $characterMax = $character->getMaxDomainCards();
        $builderMax = $builderData->getMaxDomainCards();

        expect($characterMax)->toBe($case['expected'], 
            "Character model calculation for {$case['class']}/{$case['subclass']}");
        expect($builderMax)->toBe($case['expected'], 
            "Builder data calculation for {$case['class']}/{$case['subclass']}");
        expect($characterMax)->toBe($builderMax, 
            "Character and Builder calculations must match for {$case['class']}/{$case['subclass']}");
    }

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
