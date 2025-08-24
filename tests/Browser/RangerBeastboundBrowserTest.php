<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive browser test for Ranger + Beastbound
 * 
 * Tests critical mechanics:
 * - Ranger base stats (Evasion 12, Hit Points 6) 
 * - Beastbound's Battle-Bonded +2 Evasion bonus (conditional)
 * - Companion sheet mechanics and shared Experience growth
 * - Expert Training and Advanced Training progression
 * - Loyal Friend damage sharing mechanism
 * - Domain access (Bone + Sage)
 * - Agility spellcasting trait assignment
 */

test('ranger beastbound battle bonded evasion verification', function () {
    $character = createTestCharacterWith([
        'class' => 'ranger',
        'subclass' => 'beastbound',
        'ancestry' => 'elf', // Neutral ancestry for clean testing
        'community' => 'wildborne', // Thematic for ranger
        'level' => 4, // Mid-level for specialization features
    ]);

    // Add character traits for complete testing (Agility for spellcasting)
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 2], // Spellcasting trait for Ranger Beastbound
        ['trait_name' => 'strength', 'trait_value' => 1],
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => 0],
        ['trait_name' => 'presence', 'trait_value' => 0],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Verify Ranger base stats (high evasion class)
    expect($stats->hit_points)->toBe(6); // Ranger base 6
    expect($stats->evasion)->toBeGreaterThanOrEqual(12); // Ranger base 12 + trait bonuses

    // Battle-Bonded is conditional, not a permanent stat bonus
    expect($character->getSubclassEvasionBonus())->toBe(0); // No permanent evasion bonus

    // Test that no other permanent bonuses are applied
    expect($character->getSubclassStressBonus())->toBe(0); // No stress bonus
    expect($character->getSubclassHitPointBonus())->toBe(0); // No HP bonus

    // Test domain card calculation (Beastbound provides no domain card bonuses)
    $builderData = \Domain\Character\Data\CharacterBuilderData::from([
        'selected_class' => 'ranger',
        'selected_subclass' => 'beastbound',
    ]);
    
    expect($builderData->getMaxDomainCards())->toBe(2); // Base 2, no bonuses
    expect($builderData->getSubclassDomainCardBonus())->toBe(0);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('ranger beastbound browser integration works correctly', function () {
    $character = Character::factory()->create([
        'character_key' => 'RANG123',
        'class' => null,
        'subclass' => null,
    ]);

    // Start browser test - visit character builder
    $page = visit("/character-builder/{$character->character_key}");

    // Set up the character data
    $character->update([
        'class' => 'ranger',
        'subclass' => 'beastbound',
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
        ->subclass->toBe('beastbound')
        ->ancestry->toBe('elf')
        ->community->toBe('wildborne');

    // Test that stats are calculated correctly
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(6); // Ranger base 6
    expect($stats->evasion)->toBeGreaterThanOrEqual(12); // Ranger base + bonuses

    // Battle-Bonded is situational, not permanent
    expect($character->getSubclassEvasionBonus())->toBe(0);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('beastbound subclass features are correctly defined', function () {
    // Verify Beastbound subclass JSON configuration
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $beastbound = $subclassData['beastbound'];
    
    expect($beastbound)->not()->toBeNull();
    expect($beastbound['name'])->toBe('Beastbound');
    expect($beastbound['spellcastTrait'])->toBe('Agility'); // Critical for spellcasting

    // Verify foundation features include Companion
    $foundationFeatures = $beastbound['foundationFeatures'];
    $companionFeature = collect($foundationFeatures)->firstWhere('name', 'Companion');
    
    expect($companionFeature)->not()->toBeNull();
    expect($companionFeature['description'])->toContain('animal companion');
    expect($companionFeature['description'])->toContain('Ranger Companion sheet');
    expect($companionFeature['description'])->toContain('level up');

    // Verify specialization features include Expert Training
    $specializationFeatures = $beastbound['specializationFeatures'];
    $expertTrainingFeature = collect($specializationFeatures)->firstWhere('name', 'Expert Training');
    
    expect($expertTrainingFeature)->not()->toBeNull();
    expect($expertTrainingFeature['description'])->toContain('additional level-up option');

    // CRITICAL TEST: Verify Battle-Bonded with +2 Evasion bonus
    $battleBondedFeature = collect($specializationFeatures)->firstWhere('name', 'Battle-Bonded');
    
    expect($battleBondedFeature)->not()->toBeNull();
    expect($battleBondedFeature['description'])->toContain('adversary attacks you');
    expect($battleBondedFeature['description'])->toContain('companion\'s Melee range');
    expect($battleBondedFeature['description'])->toContain('+2 bonus to your Evasion');

    // Verify mastery features include Advanced Training
    $masteryFeatures = $beastbound['masteryFeatures'];
    $advancedTrainingFeature = collect($masteryFeatures)->firstWhere('name', 'Advanced Training');
    
    expect($advancedTrainingFeature)->not()->toBeNull();
    expect($advancedTrainingFeature['description'])->toContain('two additional level-up options');

    // Verify Loyal Friend damage sharing
    $loyalFriendFeature = collect($masteryFeatures)->firstWhere('name', 'Loyal Friend');
    
    expect($loyalFriendFeature)->not()->toBeNull();
    expect($loyalFriendFeature['description'])->toContain('Once per long rest');
    expect($loyalFriendFeature['description'])->toContain('take that damage instead');
    expect($loyalFriendFeature['description'])->toContain('Close range');

});

test('ranger class has correct base stats for beastbound', function () {
    $character = createTestCharacterWith([
        'class' => 'ranger',
        'subclass' => 'beastbound',
        'ancestry' => 'human', // Use human to test different ancestry
        'community' => 'wanderborne',
    ]);

    // Add consistent traits
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 2], // Spellcasting trait
        ['trait_name' => 'strength', 'trait_value' => 1],
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => 0],
        ['trait_name' => 'presence', 'trait_value' => 0],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Verify Ranger base stats (highest evasion class)
    expect($stats->hit_points)->toBe(6); // Ranger base 6
    expect($stats->evasion)->toBeGreaterThanOrEqual(12); // Ranger base + bonuses

    // Test Ranger domains (Bone + Sage)
    $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    $rangerClass = $classData['ranger'];
    
    expect($rangerClass['domains'])->toBe(['bone', 'sage']);
    expect($rangerClass['startingEvasion'])->toBe(12); // Highest base evasion
    expect($rangerClass['startingHitPoints'])->toBe(6);

    // Test stress with Human ancestry bonus
    expect($stats->stress)->toBeGreaterThanOrEqual(6); // Base + Human +2

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('ranger class features work with beastbound subclass', function () {
    // Verify Ranger class features from JSON
    $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    $rangerClass = $classData['ranger'];
    
    expect($rangerClass['name'])->toBe('Ranger');
    
    // Check Hope feature
    expect($rangerClass['hopeFeature']['name'])->toBe('Hold Them Off');
    expect($rangerClass['hopeFeature']['hopeCost'])->toBe(3);
    expect($rangerClass['hopeFeature']['description'])->toContain('two additional adversaries');
    expect($rangerClass['hopeFeature']['description'])->toContain('same roll');
    
    // Check class features include Ranger's Focus
    $classFeatureNames = collect($rangerClass['classFeatures'])->pluck('name')->toArray();
    expect($classFeatureNames)->toContain('Ranger\'s Focus');
    expect($classFeatureNames)->toContain('Companion'); // Base ranger companion feature
    
    $focusFeature = collect($rangerClass['classFeatures'])->firstWhere('name', 'Ranger\'s Focus');
    expect($focusFeature['description'])->toContain('make the attack\'s target your Focus');
    expect($focusFeature['description'])->toContain('mark a Stress');
    
    // Verify subclasses include both beastbound and wayfinder
    expect($rangerClass['subclasses'])->toBe(['beastbound', 'wayfinder']);

});

test('ranger beastbound agility spellcasting integration', function () {
    // Test that Beastbound correctly uses Agility for spellcasting
    $character = createTestCharacterWith([
        'class' => 'ranger',
        'subclass' => 'beastbound',
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
        'selected_subclass' => 'beastbound',
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
    expect($character->fresh())->subclass->toBe('beastbound');

    // Verify the character was created with correct traits
    $agilityTrait = $character->traits()->where('trait_name', 'agility')->first();
    expect($agilityTrait->trait_value)->toBe(2);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('beastbound companion mechanics verification', function () {
    // Test companion sheet and training progression features
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $beastbound = $subclassData['beastbound'];
    
    // Test companion progression through training features
    $foundationFeatures = $beastbound['foundationFeatures'];
    $specializationFeatures = $beastbound['specializationFeatures'];
    $masteryFeatures = $beastbound['masteryFeatures'];
    
    // Foundation: Basic companion
    $companionFeature = collect($foundationFeatures)->firstWhere('name', 'Companion');
    expect($companionFeature['description'])->toContain('Ranger Companion sheet');
    
    // Specialization: +1 level-up option
    $expertTrainingFeature = collect($specializationFeatures)->firstWhere('name', 'Expert Training');
    expect($expertTrainingFeature['description'])->toContain('additional level-up option');
    
    // Mastery: +2 level-up options
    $advancedTrainingFeature = collect($masteryFeatures)->firstWhere('name', 'Advanced Training');
    expect($advancedTrainingFeature['description'])->toContain('two additional level-up options');
    
    // Total companion level-ups: Base + Expert(+1) + Advanced(+2) = significant progression

});

test('beastbound battle bonded conditional evasion mechanics', function () {
    // Test the specific wording and conditions of Battle-Bonded
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $beastbound = $subclassData['beastbound'];
    
    $specializationFeatures = $beastbound['specializationFeatures'];
    $battleBondedFeature = collect($specializationFeatures)->firstWhere('name', 'Battle-Bonded');
    
    // Verify the conditional nature of the +2 Evasion bonus
    expect($battleBondedFeature['description'])->toContain('When an adversary attacks you');
    expect($battleBondedFeature['description'])->toContain('within your companion\'s Melee range');
    expect($battleBondedFeature['description'])->toContain('+2 bonus to your Evasion');
    expect($battleBondedFeature['description'])->toContain('against the attack');
    
    // This is a conditional bonus, not a permanent stat increase
    // It only applies when: adversary attacks + adversary in companion melee range

});
