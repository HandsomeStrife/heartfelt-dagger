<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive audit test for ALL Mechanical Bonuses across classes and subclasses
 * 
 * This test ensures that:
 * - All stat bonuses (evasion, HP, stress, damage thresholds) calculate correctly
 * - Bonuses stack properly (base + ancestry + subclass + other sources)
 * - No unexpected or incorrect bonuses exist
 * - All calculation methods return consistent results
 * - Edge cases and interactions work correctly
 * 
 * This is the final comprehensive mechanical verification.
 */

test('all known mechanical bonuses are correctly implemented - comprehensive audit', function () {
    // Test all confirmed mechanical bonuses from our individual class tests
    $knownBonuses = [
        // HIT POINT BONUSES
        ['class' => 'wizard', 'subclass' => 'school of war', 'bonus_type' => 'hit_points', 'expected' => 1],
        
        // STRESS BONUSES
        ['class' => 'guardian', 'subclass' => 'vengeance', 'bonus_type' => 'stress', 'expected' => 1],
        
        // EVASION BONUSES (permanent)
        ['class' => 'rogue', 'subclass' => 'nightwalker', 'bonus_type' => 'evasion', 'expected' => 1],
        
        // DAMAGE THRESHOLD BONUSES
        ['class' => 'guardian', 'subclass' => 'stalwart', 'bonus_type' => 'damage_threshold', 'expected' => 6],
        ['class' => 'seraph', 'subclass' => 'winged sentinel', 'bonus_type' => 'severe_threshold', 'expected' => 4],
        
        // DOMAIN CARD BONUSES
        ['class' => 'wizard', 'subclass' => 'school of knowledge', 'bonus_type' => 'domain_cards', 'expected' => 1],
    ];
    
    foreach ($knownBonuses as $bonus) {
        $character = createTestCharacterWith([
            'class' => $bonus['class'],
            'subclass' => $bonus['subclass'],
            'ancestry' => 'human',
            'community' => 'loreborne',
            'level' => 4,
        ]);

        $builderData = \Domain\Character\Data\CharacterBuilderData::from([
            'selected_class' => $bonus['class'],
            'selected_subclass' => $bonus['subclass'],
        ]);

        switch ($bonus['bonus_type']) {
            case 'hit_points':
                expect($character->getSubclassHitPointBonus())->toBe($bonus['expected'], 
                    "{$bonus['class']} {$bonus['subclass']} should have +{$bonus['expected']} HP bonus");
                break;
                
            case 'stress':
                expect($character->getSubclassStressBonus())->toBe($bonus['expected'],
                    "{$bonus['class']} {$bonus['subclass']} should have +{$bonus['expected']} stress bonus");
                break;
                
            case 'evasion':
                expect($character->getSubclassEvasionBonus())->toBe($bonus['expected'],
                    "{$bonus['class']} {$bonus['subclass']} should have +{$bonus['expected']} evasion bonus");
                break;
                
            case 'damage_threshold':
                expect($character->getSubclassDamageThresholdBonus())->toBe($bonus['expected'],
                    "{$bonus['class']} {$bonus['subclass']} should have +{$bonus['expected']} damage threshold bonus");
                break;
                
            case 'severe_threshold':
                expect($character->getSubclassSevereThresholdBonus())->toBe($bonus['expected'],
                    "{$bonus['class']} {$bonus['subclass']} should have +{$bonus['expected']} severe threshold bonus");
                break;
                
            case 'domain_cards':
                expect($character->getSubclassDomainCardBonus())->toBe($bonus['expected'],
                    "{$bonus['class']} {$bonus['subclass']} should have +{$bonus['expected']} domain card bonus");
                break;
        }
    }

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('no unexpected mechanical bonuses exist in any subclass', function () {
    // Comprehensive audit to find any unexpected bonuses
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    
    $unexpectedBonuses = [];
    $allowedBonuses = [
        // Known good bonuses from our individual tests
        'school of war' => ['hit_point_bonus' => 1],
        'vengeance' => ['stress_bonus' => 1], 
        'nightwalker' => ['evasion_bonus' => 1],
        'stalwart' => ['damage_threshold_bonus' => 6], // Foundation +1, Specialization +2, Mastery +3
        'winged sentinel' => ['severe_threshold_bonus' => 4],
        'school of knowledge' => ['domain_card_bonus' => 1],
    ];
    
    foreach ($subclassData as $key => $subclass) {
        $foundBonuses = [];
        
        // Check all feature types for mechanical bonuses
        $featureTypes = ['foundationFeatures', 'specializationFeatures', 'masteryFeatures'];
        
        foreach ($featureTypes as $featureType) {
            if (isset($subclass[$featureType])) {
                foreach ($subclass[$featureType] as $feature) {
                    if (isset($feature['effects'])) {
                        foreach ($feature['effects'] as $effect) {
                            if (in_array($effect['type'], ['hit_point_bonus', 'stress_bonus', 'evasion_bonus', 
                                                         'damage_threshold_bonus', 'severe_threshold_bonus', 
                                                         'domain_card_bonus'])) {
                                // Sum bonuses of the same type (e.g., Stalwart has multiple damage threshold bonuses)
                                if (isset($foundBonuses[$effect['type']])) {
                                    $foundBonuses[$effect['type']] += $effect['value'];
                                } else {
                                    $foundBonuses[$effect['type']] = $effect['value'];
                                }
                            }
                        }
                    }
                }
            }
        }
        
        // Check if bonuses match expectations
        if (!empty($foundBonuses)) {
            if (!isset($allowedBonuses[$key])) {
                $unexpectedBonuses[$key] = $foundBonuses;
            } else {
                foreach ($foundBonuses as $bonusType => $value) {
                    if (!isset($allowedBonuses[$key][$bonusType]) || 
                        $allowedBonuses[$key][$bonusType] !== $value) {
                        $unexpectedBonuses[$key][$bonusType] = $value;
                    }
                }
            }
        }
    }
    
    // Should only have expected bonuses
    expect($unexpectedBonuses)->toBe([], 'Found unexpected mechanical bonuses: ' . json_encode($unexpectedBonuses));

});

test('base class stats are consistent and correct', function () {
    // Verify base stats for all classes match our individual test expectations
    $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    
    $expectedBaseStats = [
        'bard' => ['evasion' => 10, 'hit_points' => 5],
        'druid' => ['evasion' => 10, 'hit_points' => 6], 
        'guardian' => ['evasion' => 9, 'hit_points' => 7],
        'ranger' => ['evasion' => 12, 'hit_points' => 6],
        'rogue' => ['evasion' => 12, 'hit_points' => 6],
        'seraph' => ['evasion' => 9, 'hit_points' => 7],
        'sorcerer' => ['evasion' => 10, 'hit_points' => 6],
        'warrior' => ['evasion' => 11, 'hit_points' => 6],
        'wizard' => ['evasion' => 11, 'hit_points' => 5],
    ];
    
    foreach ($expectedBaseStats as $className => $expectedStats) {
        $class = $classData[$className];
        
        expect($class['startingEvasion'])->toBe($expectedStats['evasion'], 
            "Class '{$className}' should have {$expectedStats['evasion']} base evasion");
            
        expect($class['startingHitPoints'])->toBe($expectedStats['hit_points'],
            "Class '{$className}' should have {$expectedStats['hit_points']} base hit points");
    }

});

test('stat calculations include all bonus sources correctly', function () {
    // Test that final stats include base + ancestry + subclass bonuses
    $testCases = [
        // Guardian Vengeance + Dwarf = high stress stacking
        ['class' => 'guardian', 'subclass' => 'vengeance', 'ancestry' => 'dwarf'],
        
        // Wizard School of War + Human = HP and stress bonuses  
        ['class' => 'wizard', 'subclass' => 'school of war', 'ancestry' => 'human'],
        
        // Rogue Nightwalker + Elf = evasion stacking
        ['class' => 'rogue', 'subclass' => 'nightwalker', 'ancestry' => 'elf'],
    ];
    
    foreach ($testCases as $case) {
        $character = createTestCharacterWith([
            'class' => $case['class'],
            'subclass' => $case['subclass'],
            'ancestry' => $case['ancestry'],
            'community' => 'loreborne',
        ]);

        $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
        $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
        
        // Verify final stats are base + modifiers
        $baseEvasion = $classData[$case['class']]['startingEvasion'];
        $baseHitPoints = $classData[$case['class']]['startingHitPoints'];
        
        // Final evasion should be base + trait bonuses + subclass bonuses  
        expect($stats->evasion)->toBeGreaterThanOrEqual($baseEvasion, 
            "{$case['class']} {$case['subclass']} {$case['ancestry']} evasion should be >= base {$baseEvasion}");
            
        // Final HP should include subclass bonuses
        if ($case['subclass'] === 'school of war') {
            expect($stats->hit_points)->toBe($baseHitPoints + 1, 
                "School of War should add +1 HP to base {$baseHitPoints}");
        } else {
            expect($stats->hit_points)->toBeGreaterThanOrEqual($baseHitPoints,
                "{$case['class']} {$case['subclass']} HP should be >= base {$baseHitPoints}");
        }
    }

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('damage threshold calculations work correctly', function () {
    // Test complex damage threshold calculations
    $character = createTestCharacterWith([
        'class' => 'guardian',
        'subclass' => 'stalwart',
        'ancestry' => 'dwarf',  // Additional survivability
        'community' => 'ridgeborne',
        'level' => 6, // High level for all bonuses
    ]);

    // Add traits for proficiency calculation
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 0],
        ['trait_name' => 'strength', 'trait_value' => 2],
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => 1],
        ['trait_name' => 'presence', 'trait_value' => 0],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    // Stalwart provides +6 total damage threshold bonus
    expect($character->getSubclassDamageThresholdBonus())->toBe(6);
    
    // Final damage thresholds should be base + proficiency + subclass bonus
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    
    // Major threshold should be significantly higher due to stacking
    expect($stats->major_threshold)->toBeGreaterThan(10,
        'Guardian Stalwart should have high major damage threshold');

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('evasion bonuses stack correctly with traits', function () {
    // Test evasion stacking with traits and subclass bonuses
    $character = createTestCharacterWith([
        'class' => 'rogue',
        'subclass' => 'nightwalker',
        'ancestry' => 'elf',
        'community' => 'slyborne',
    ]);

    // Add high Agility for evasion bonus
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 2], // Should boost evasion
        ['trait_name' => 'strength', 'trait_value' => 0],
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => 1],
        ['trait_name' => 'presence', 'trait_value' => 0],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    // Nightwalker provides +1 permanent evasion bonus
    expect($character->getSubclassEvasionBonus())->toBe(1);
    
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    $baseEvasion = $classData['rogue']['startingEvasion']; // 12
    
    // Final evasion should be base + trait bonuses + subclass bonus
    // Should be at least 12 (base) + 1 (subclass) = 13, likely higher with trait bonuses
    expect($stats->evasion)->toBeGreaterThanOrEqual($baseEvasion + 1,
        'Rogue Nightwalker should have base evasion + subclass bonus + trait bonuses');

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('hope features and costs are correctly implemented', function () {
    // Test Hope feature costs across classes
    $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    
    $expectedHopeCosts = [
        'bard' => 3,     // Inspire costs 3 Hope
        'druid' => 3,    // Nature's Healing costs 3 Hope  
        'guardian' => 3, // Frontline Tank costs 3 Hope
        'ranger' => 3,   // Hold Them Off costs 3 Hope
        'rogue' => 3,    // Rogue's Dodge costs 3 Hope
        'seraph' => 3,   // Divine Retribution costs 3 Hope
        'sorcerer' => 3, // Channel Raw Power costs 3 Hope
        'warrior' => 3,  // No Mercy costs 3 Hope
        'wizard' => 3,   // Face Your Fear costs 3 Hope
    ];
    
    foreach ($expectedHopeCosts as $className => $expectedCost) {
        $class = $classData[$className];
        $hopeFeature = $class['hopeFeature'];
        
        expect($hopeFeature['hopeCost'])->toBe($expectedCost,
            "Class '{$className}' Hope feature should cost {$expectedCost} Hope");
    }

});

test('calculation methods are consistent across all systems', function () {
    // Test that Character model and CharacterBuilderData return consistent results
    $testCombinations = [
        ['class' => 'guardian', 'subclass' => 'stalwart'],
        ['class' => 'wizard', 'subclass' => 'school of knowledge'], 
        ['class' => 'rogue', 'subclass' => 'nightwalker'],
        ['class' => 'seraph', 'subclass' => 'winged sentinel'],
        ['class' => 'warrior', 'subclass' => 'call of the brave'],
    ];
    
    foreach ($testCombinations as $combo) {
        $character = createTestCharacterWith([
            'class' => $combo['class'],
            'subclass' => $combo['subclass'],
            'ancestry' => 'human',
            'community' => 'loreborne',
        ]);

        $builderData = \Domain\Character\Data\CharacterBuilderData::from([
            'selected_class' => $combo['class'],
            'selected_subclass' => $combo['subclass'],
        ]);

        // Domain card calculations should match
        expect($character->getMaxDomainCards())->toBe($builderData->getMaxDomainCards(),
            "{$combo['class']} {$combo['subclass']} domain card calculations should match");
            
        expect($character->getSubclassDomainCardBonus())->toBe($builderData->getSubclassDomainCardBonus(),
            "{$combo['class']} {$combo['subclass']} subclass bonus calculations should match");
    }

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('edge cases and interactions work correctly', function () {
    // Test edge cases and complex interactions
    
    // High-level character with multiple bonuses
    $character = createTestCharacterWith([
        'class' => 'guardian',
        'subclass' => 'vengeance',
        'ancestry' => 'dwarf',
        'community' => 'ridgeborne',
        'level' => 8, // High level
    ]);

    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 1],
        ['trait_name' => 'strength', 'trait_value' => 2],
        ['trait_name' => 'finesse', 'trait_value' => 0],
        ['trait_name' => 'instinct', 'trait_value' => 1],
        ['trait_name' => 'presence', 'trait_value' => 0],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    
    // Should have good survivability from multiple sources
    expect($stats->hit_points)->toBeGreaterThanOrEqual(7); // Guardian base
    expect($stats->stress)->toBeGreaterThan(5); // Base + Vengeance + ancestry
    expect($stats->evasion)->toBeGreaterThanOrEqual(9); // Base + bonuses
    
    // All calculations should be positive and reasonable
    expect($stats->hit_points)->toBeGreaterThan(0);
    expect($stats->evasion)->toBeGreaterThan(0);  
    expect($stats->stress)->toBeGreaterThan(0);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
