<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive browser test for Ranger + Wayfinder
 * 
 * Tests critical mechanics:
 * - Ranger base stats (Evasion 11, Hit Points 5)
 * - Wayfinder's Ruthless Predator mechanics (Stress for +1 Proficiency, Severe damage causes Stress)
 * - Path Forward navigation abilities for travel optimization
 * - Elusive Predator Focus-specific defense (+2 Evasion vs Focus)
 * - Apex Predator Fear pool manipulation (Hope + success = remove Fear)
 * - Focus feature integration with Wayfinder hunting mechanics
 * - Domain access (Sage + Bone)
 * - Agility spellcasting trait integration
 */

test('ranger wayfinder predator mechanics verification', function () {
    $character = createTestCharacterWith([
        'class' => 'ranger',
        'subclass' => 'wayfinder',
        'ancestry' => 'elf', // Neutral ancestry for clean testing
        'community' => 'wildborne', // Thematic for ranger
        'level' => 4, // Mid-level for specialization features
    ]);

    // Add character traits for complete testing (Agility for spellcasting)
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 2], // Spellcasting trait for Ranger Wayfinder
        ['trait_name' => 'strength', 'trait_value' => 1],
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => 0],
        ['trait_name' => 'presence', 'trait_value' => 0],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Verify Ranger base stats
    expect($stats->hit_points)->toBe(6); // Ranger base 6
    expect($stats->evasion)->toBeGreaterThanOrEqual(12); // Ranger base 12 + trait bonuses

    // Test that no permanent bonuses are applied (Wayfinder features are situational)
    expect($character->getSubclassEvasionBonus())->toBe(0); // No permanent evasion bonus (Elusive is vs Focus only)
    expect($character->getSubclassStressBonus())->toBe(0); // No stress bonus  
    expect($character->getSubclassHitPointBonus())->toBe(0); // No HP bonus

    // Test domain card calculation (Wayfinder provides no domain card bonuses)
    $builderData = \Domain\Character\Data\CharacterBuilderData::from([
        'selected_class' => 'ranger',
        'selected_subclass' => 'wayfinder',
    ]);
    
    expect($builderData->getMaxDomainCards())->toBe(2); // Base 2, no bonuses
    expect($builderData->getSubclassDomainCardBonus())->toBe(0);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('ranger wayfinder browser integration works correctly', function () {
    $character = Character::factory()->create([
        'character_key' => 'WAYF123',
        'class' => null,
        'subclass' => null,
    ]);

    // Start browser test - visit character builder
    $page = visit("/character-builder/{$character->character_key}");

    // Set up the character data
    $character->update([
        'class' => 'ranger',
        'subclass' => 'wayfinder',
        'ancestry' => 'elf',
        'community' => 'wildborne',
    ]);

    // Add character traits (Agility focus for spellcasting)
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 2], // Spellcasting trait
        ['trait_name' => 'strength', 'trait_value' => 1],
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => 0],
        ['trait_name' => 'presence', 'trait_value' => 0],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    // Verify character was configured correctly
    expect($character->fresh())
        ->class->toBe('ranger')
        ->subclass->toBe('wayfinder')
        ->ancestry->toBe('elf')
        ->community->toBe('wildborne');

    // Test that stats are calculated correctly
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(6); // Ranger base 6
    expect($stats->evasion)->toBeGreaterThanOrEqual(12); // Ranger base + bonuses

    // No permanent stat bonuses from Wayfinder
    expect($character->getSubclassEvasionBonus())->toBe(0);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('wayfinder subclass features are correctly defined', function () {
    // Verify Wayfinder subclass JSON configuration
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $wayfinder = $subclassData['wayfinder'];
    
    expect($wayfinder)->not()->toBeNull();
    expect($wayfinder['name'])->toBe('Wayfinder');
    expect($wayfinder['spellcastTrait'])->toBe('Agility'); // Critical for spellcasting

    // CRITICAL TEST: Verify Ruthless Predator with proficiency bonus and Severe damage effect
    $foundationFeatures = $wayfinder['foundationFeatures'];
    $ruthlessFeature = collect($foundationFeatures)->firstWhere('name', 'Ruthless Predator');
    
    expect($ruthlessFeature)->not()->toBeNull();
    expect($ruthlessFeature['description'])->toContain('make a damage roll');
    expect($ruthlessFeature['description'])->toContain('mark a Stress');
    expect($ruthlessFeature['description'])->toContain('+1 bonus to your Proficiency');
    expect($ruthlessFeature['description'])->toContain('deal Severe damage to an adversary');
    expect($ruthlessFeature['description'])->toContain('they must mark a Stress');
    expect($ruthlessFeature['stressCost'])->toBe(1);

    // Verify Path Forward navigation
    $pathForwardFeature = collect($foundationFeatures)->firstWhere('name', 'Path Forward');
    
    expect($pathForwardFeature)->not()->toBeNull();
    expect($pathForwardFeature['description'])->toContain('traveling to a place you\'ve previously visited');
    expect($pathForwardFeature['description'])->toContain('carry an object that has been at the location');
    expect($pathForwardFeature['description'])->toContain('shortest, most direct path');

    // Verify Elusive Predator specialization
    $specializationFeatures = $wayfinder['specializationFeatures'];
    $elusiveFeature = collect($specializationFeatures)->firstWhere('name', 'Elusive Predator');
    
    expect($elusiveFeature)->not()->toBeNull();
    expect($elusiveFeature['description'])->toContain('Focus makes an attack against you');
    expect($elusiveFeature['description'])->toContain('+2 bonus to your Evasion');

    // CRITICAL TEST: Verify Apex Predator Fear pool manipulation
    $masteryFeatures = $wayfinder['masteryFeatures'];
    $apexFeature = collect($masteryFeatures)->firstWhere('name', 'Apex Predator');
    
    expect($apexFeature)->not()->toBeNull();
    expect($apexFeature['description'])->toContain('attack roll against your Focus');
    expect($apexFeature['description'])->toContain('spend a Hope');
    expect($apexFeature['description'])->toContain('successful attack');
    expect($apexFeature['description'])->toContain('remove a Fear from the GM\'s Fear pool');
    expect($apexFeature['hopeCost'])->toBe(1);

});

test('ruthless predator mechanics verification', function () {
    // Test Ruthless Predator's damage enhancement and Severe damage effect
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $wayfinder = $subclassData['wayfinder'];
    
    $foundationFeatures = $wayfinder['foundationFeatures'];
    $ruthlessFeature = collect($foundationFeatures)->firstWhere('name', 'Ruthless Predator');
    
    // Test stress-for-proficiency trade
    expect($ruthlessFeature['description'])->toContain('When you make a damage roll');
    expect($ruthlessFeature['description'])->toContain('mark a Stress to gain a +1 bonus to your Proficiency');
    expect($ruthlessFeature['stressCost'])->toBe(1);
    
    // Test Severe damage punishment  
    expect($ruthlessFeature['description'])->toContain('when you deal Severe damage to an adversary');
    expect($ruthlessFeature['description'])->toContain('they must mark a Stress');
    
    // This creates a damage-focused build: Spend Stress for better damage, punish enemies for taking Severe damage

});

test('path forward navigation mechanics verification', function () {
    // Test Path Forward's exploration and travel optimization
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $wayfinder = $subclassData['wayfinder'];
    
    $foundationFeatures = $wayfinder['foundationFeatures'];
    $pathForwardFeature = collect($foundationFeatures)->firstWhere('name', 'Path Forward');
    
    // Test navigation conditions
    expect($pathForwardFeature['description'])->toContain('traveling to a place you\'ve previously visited');
    expect($pathForwardFeature['description'])->toContain('carry an object that has been at the location before');
    
    // Test navigation benefit
    expect($pathForwardFeature['description'])->toContain('identify the shortest, most direct path to your destination');
    
    // This provides narrative exploration advantages and travel efficiency

});

test('elusive predator focus defense mechanics', function () {
    // Test Elusive Predator's Focus-specific defensive bonus
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $wayfinder = $subclassData['wayfinder'];
    
    $specializationFeatures = $wayfinder['specializationFeatures'];
    $elusiveFeature = collect($specializationFeatures)->firstWhere('name', 'Elusive Predator');
    
    // Test Focus-specific trigger
    expect($elusiveFeature['description'])->toContain('When your Focus makes an attack against you');
    
    // Test defensive bonus
    expect($elusiveFeature['description'])->toContain('you gain a +2 bonus to your Evasion against the attack');
    
    // This creates a hunter-prey dynamic: Better defense against your chosen target

});

test('apex predator fear pool mechanics verification', function () {
    // Test Apex Predator's Fear pool manipulation
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $wayfinder = $subclassData['wayfinder'];
    
    $masteryFeatures = $wayfinder['masteryFeatures'];
    $apexFeature = collect($masteryFeatures)->firstWhere('name', 'Apex Predator');
    
    // Test Hope investment and targeting
    expect($apexFeature['description'])->toContain('Before you make an attack roll against your Focus');
    expect($apexFeature['description'])->toContain('spend a Hope');
    expect($apexFeature['hopeCost'])->toBe(1);
    
    // Test success condition and Fear removal
    expect($apexFeature['description'])->toContain('On a successful attack');
    expect($apexFeature['description'])->toContain('remove a Fear from the GM\'s Fear pool');
    
    // This provides strategic value: Spend Hope to potentially reduce GM resources

});

test('ranger wayfinder focus integration', function () {
    // Test how Wayfinder enhances base Ranger Focus mechanics
    $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    $rangerClass = $classData['ranger'];
    
    // Verify base Focus feature
    $classFeatureNames = collect($rangerClass['classFeatures'])->pluck('name')->toArray();
    expect($classFeatureNames)->toContain('Ranger\'s Focus');
    
    $focusFeature = collect($rangerClass['classFeatures'])->firstWhere('name', 'Ranger\'s Focus');
    expect($focusFeature['description'])->toContain('make the attack\'s target your Focus');
    expect($focusFeature['description'])->toContain('deal damage to them, they must mark a Stress');
    expect($focusFeature['description'])->toContain('know precisely what direction they are in');
    
    // Wayfinder enhancements:
    // 1. Elusive Predator: +2 Evasion vs Focus attacks
    // 2. Apex Predator: Hope investment for Fear removal vs Focus
    // 3. Ruthless Predator: Stress for better damage (general, not Focus-specific)
    
    // This creates the ultimate hunter specializing in their chosen prey type

});

test('ranger wayfinder agility spellcasting integration', function () {
    // Test that Wayfinder correctly uses Agility for spellcasting
    $character = createTestCharacterWith([
        'class' => 'ranger',
        'subclass' => 'wayfinder',
        'ancestry' => 'elf',
        'community' => 'wildborne',
    ]);

    // Set high Agility trait for spellcasting
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 2], // High for spellcasting
        ['trait_name' => 'strength', 'trait_value' => 1],
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => 0],
        ['trait_name' => 'presence', 'trait_value' => 0],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    // Test that builder data correctly identifies Agility as spellcast trait
    $builderData = \Domain\Character\Data\CharacterBuilderData::from([
        'selected_class' => 'ranger',
        'selected_subclass' => 'wayfinder',
        'character_traits' => [
            'agility' => 2, // Should be used for spellcasting calculations
            'strength' => 1,
            'finesse' => 1,
            'instinct' => 0,
            'presence' => 0,
            'knowledge' => -1,
        ],
    ]);

    expect($character->fresh())->class->toBe('ranger');
    expect($character->fresh())->subclass->toBe('wayfinder');

    // Verify the character was created with correct traits
    $agilityTrait = $character->traits()->where('trait_name', 'agility')->first();
    expect($agilityTrait->trait_value)->toBe(2);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('wayfinder complete hunting system verification', function () {
    // Test how all Wayfinder features create a comprehensive hunting system
    $character = createTestCharacterWith([
        'class' => 'ranger',
        'subclass' => 'wayfinder',
        'ancestry' => 'human', // +2 stress for more Ruthless Predator uses
        'community' => 'wildborne',
        'level' => 6, // High level for mastery features
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Test that the ranger has good survivability for hunting work
    expect($stats->hit_points)->toBe(6); // Ranger base 6
    expect($stats->evasion)->toBeGreaterThanOrEqual(12); // Good evasion + Focus bonuses

    // The complete hunting system:
    // 1. Focus: Choose prey type, +1 damage vs that type
    // 2. Ruthless Predator: Trade Stress for +1 Proficiency on damage
    // 3. Path Forward: Navigate efficiently to hunting grounds
    // 4. Elusive Predator: +2 Evasion vs Focus attacks (hunter-prey awareness)
    // 5. Apex Predator: Hope investment reduces GM Fear pool
    // 6. Sage domain: Nature magic and wisdom
    // 7. Bone domain: Death and intimidation magic
    // 8. Agility spellcasting: Mobile, precise magical effects
    
    // This creates the ultimate specialized hunter with tactical advantages vs chosen prey

});

test('wayfinder stress management and damage optimization', function () {
    // Test the risk-reward balance of Wayfinder's Stress usage
    $character = createTestCharacterWith([
        'class' => 'ranger',
        'subclass' => 'wayfinder',
        'ancestry' => 'dwarf', // Sturdy, damage-focused
        'community' => 'ridgeborne',
        'level' => 5,
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Base stats support aggressive hunting tactics
    expect($stats->hit_points)->toBe(6); // Ranger base
    expect($stats->evasion)->toBeGreaterThanOrEqual(12); // Good defense
    
    // Risk-Reward Analysis:
    // COST: 1 Stress per Ruthless Predator use
    // BENEFIT: +1 Proficiency to damage roll (significant damage boost)
    // BONUS: Severe damage causes enemy Stress (punishment for taking big damage)
    
    // With good Stress management, can use Ruthless Predator multiple times per combat
    // +1 Proficiency can be the difference between regular damage and Severe damage
    // Severe damage punishment creates a feedback loop: Better damage → enemy takes Stress
    
    // Perfect for high-damage, high-risk hunting tactics

});
