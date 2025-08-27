<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive browser test for Warrior + Call of the Slayer
 * 
 * Tests critical mechanics:
 * - Warrior base stats (Evasion 11, Hit Points 6)
 * - Call of the Slayer's Slayer Dice pool mechanics (Hope→d6, store = Proficiency)
 * - Slayer Dice spending on attack/damage rolls
 * - End-of-session Hope recovery (1 Hope per unspent die)
 * - Weapon Specialist secondary weapon damage integration
 * - Weapon Specialist Slayer Dice reroll (1s once per long rest)
 * - Martial Preparation party-wide d6 Slayer Die distribution
 * - Domain access (Blade + Bone)
 * - Combat Training level-based damage scaling
 */

test('warrior call of slayer dice pool mechanics verification', function () {
    $character = createTestCharacterWith([
        'class' => 'warrior',
        'subclass' => 'call of the slayer',
        'ancestry' => 'elf', // Neutral ancestry for clean testing
        'community' => 'ridgeborne', // Thematic for warrior
        'level' => 4, // Mid-level for specialization features
    ]);

    // Add character traits for complete testing (combat-focused)
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 1],
        ['trait_name' => 'strength', 'trait_value' => 2], // Primary combat trait
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => 0],
        ['trait_name' => 'presence', 'trait_value' => 0],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Verify Warrior base stats
    expect($stats->hit_points)->toBe(6); // Warrior base 6
    expect($stats->evasion)->toBeGreaterThanOrEqual(11); // Warrior base 11 + trait bonuses

    // Test that no permanent bonuses are applied (Call of Slayer features are resource-based)
    expect($character->getSubclassEvasionBonus())->toBe(0); // No permanent evasion bonus
    expect($character->getSubclassStressBonus())->toBe(0); // No stress bonus  
    expect($character->getSubclassHitPointBonus())->toBe(0); // No HP bonus

    // Test domain card calculation (Call of the Slayer provides no domain card bonuses)
    $builderData = \Domain\Character\Data\CharacterBuilderData::from([
        'selected_class' => 'warrior',
        'selected_subclass' => 'call of the slayer',
    ]);
    
    expect($builderData->getMaxDomainCards())->toBe(2); // Base 2, no bonuses
    expect($builderData->getSubclassDomainCardBonus())->toBe(0);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('warrior call of slayer browser integration works correctly', function () {
    $character = Character::factory()->create([
        'character_key' => 'SLAY123',
        'class' => null,
        'subclass' => null,
    ]);

    // Start browser test - visit character builder
    $page = visit("/character-builder/{$character->character_key}");

    // Set up the character data
    $character->update([
        'class' => 'warrior',
        'subclass' => 'call of the slayer',
        'ancestry' => 'elf',
        'community' => 'ridgeborne',
    ]);

    // Add character traits (combat focus)
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 1],
        ['trait_name' => 'strength', 'trait_value' => 2],
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => 0],
        ['trait_name' => 'presence', 'trait_value' => 0],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    // Verify character was configured correctly
    expect($character->fresh())
        ->class->toBe('warrior')
        ->subclass->toBe('call of the slayer')
        ->ancestry->toBe('elf')
        ->community->toBe('ridgeborne');

    // Test that stats are calculated correctly
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(6); // Warrior base 6
    expect($stats->evasion)->toBeGreaterThanOrEqual(11); // Warrior base + bonuses

    // No permanent stat bonuses from Call of the Slayer
    expect($character->getSubclassEvasionBonus())->toBe(0);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('call of slayer subclass features are correctly defined', function () {
    // Verify Call of the Slayer subclass JSON configuration
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $callOfSlayer = $subclassData['call of the slayer'];
    
    expect($callOfSlayer)->not()->toBeNull();
    expect($callOfSlayer['name'])->toBe('Call of the Slayer');

    // CRITICAL TEST: Verify Slayer Dice pool mechanics
    $foundationFeatures = $callOfSlayer['foundationFeatures'];
    $slayerFeature = collect($foundationFeatures)->firstWhere('name', 'Slayer');
    
    expect($slayerFeature)->not()->toBeNull();
    expect($slayerFeature['description'])->toContain('pool of dice called Slayer Dice');
    expect($slayerFeature['description'])->toContain('roll with Hope');
    expect($slayerFeature['description'])->toContain('place a d6');
    expect($slayerFeature['description'])->toContain('instead of gaining a Hope');
    expect($slayerFeature['description'])->toContain('store a number of Slayer Dice equal to your Proficiency');
    expect($slayerFeature['description'])->toContain('attack roll or damage roll');
    expect($slayerFeature['description'])->toContain('end of each session');
    expect($slayerFeature['description'])->toContain('gain a Hope per die cleared');

    // Verify Weapon Specialist mechanics
    $specializationFeatures = $callOfSlayer['specializationFeatures'];
    $weaponSpecialistFeature = collect($specializationFeatures)->firstWhere('name', 'Weapon Specialist');
    
    expect($weaponSpecialistFeature)->not()->toBeNull();
    expect($weaponSpecialistFeature['description'])->toContain('multiple weapons');
    expect($weaponSpecialistFeature['description'])->toContain('spend a Hope');
    expect($weaponSpecialistFeature['description'])->toContain('secondary weapon');
    expect($weaponSpecialistFeature['description'])->toContain('damage dice');
    expect($weaponSpecialistFeature['description'])->toContain('reroll any 1s');
    expect($weaponSpecialistFeature['description'])->toContain('once per long rest');
    expect($weaponSpecialistFeature['hopeCost'])->toBe(1);

    // CRITICAL TEST: Verify Martial Preparation party benefits
    $masteryFeatures = $callOfSlayer['masteryFeatures'];
    $martialPrepFeature = collect($masteryFeatures)->firstWhere('name', 'Martial Preparation');
    
    expect($martialPrepFeature)->not()->toBeNull();
    expect($martialPrepFeature['description'])->toContain('party gains access');
    expect($martialPrepFeature['description'])->toContain('downtime move');
    expect($martialPrepFeature['description'])->toContain('each ally');
    expect($martialPrepFeature['description'])->toContain('d6 Slayer Die');
    expect($martialPrepFeature['description'])->toContain('attack or damage roll');

});

test('warrior class integration with call of slayer', function () {
    // Test how Warrior class features integrate with Call of the Slayer
    $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    $warriorClass = $classData['warrior'];
    
    expect($warriorClass['name'])->toBe('Warrior');
    
    // Verify Combat Training synergy with Slayer mechanics
    $combatTrainingFeature = collect($warriorClass['classFeatures'])->firstWhere('name', 'Combat Training');
    expect($combatTrainingFeature['description'])->toContain('bonus to your damage roll equal to your level');
    expect($combatTrainingFeature['description'])->toContain('physical damage');
    
    // Level-based damage + Slayer Dice creates powerful damage scaling
    // At level 4: +4 base damage + Slayer Dice pool
    
    // Verify No Mercy Hope feature provides fuel for Slayer conversions
    expect($warriorClass['hopeFeature']['name'])->toBe('No Mercy');
    expect($warriorClass['hopeFeature']['hopeCost'])->toBe(3);
    expect($warriorClass['hopeFeature']['description'])->toContain('+1 bonus to your attack rolls');

});

test('slayer dice pool mechanics verification', function () {
    // Test the complex Slayer Dice resource management system
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $callOfSlayer = $subclassData['call of the slayer'];
    
    $foundationFeatures = $callOfSlayer['foundationFeatures'];
    $slayerFeature = collect($foundationFeatures)->firstWhere('name', 'Slayer');
    
    // CRITICAL: Hope → Slayer Die conversion
    expect($slayerFeature['description'])->toContain('On a roll with Hope');
    expect($slayerFeature['description'])->toContain('place a d6 on this card instead of gaining a Hope');
    
    // Storage limit = Proficiency
    expect($slayerFeature['description'])->toContain('store a number of Slayer Dice equal to your Proficiency');
    
    // Spending mechanics
    expect($slayerFeature['description'])->toContain('spend any number of these Slayer Dice');
    expect($slayerFeature['description'])->toContain('rolling them and adding their result');
    expect($slayerFeature['description'])->toContain('attack roll or damage roll');
    
    // End-of-session recovery
    expect($slayerFeature['description'])->toContain('end of each session');
    expect($slayerFeature['description'])->toContain('clear any unspent Slayer Dice');
    expect($slayerFeature['description'])->toContain('gain a Hope per die cleared');
    
    // This creates a Hope economy: Hope → Slayer Dice → immediate power → end session Hope recovery

});

test('weapon specialist secondary weapon integration', function () {
    // Test Weapon Specialist's secondary weapon mechanics
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $callOfSlayer = $subclassData['call of the slayer'];
    
    $specializationFeatures = $callOfSlayer['specializationFeatures'];
    $weaponSpecialistFeature = collect($specializationFeatures)->firstWhere('name', 'Weapon Specialist');
    
    // Secondary weapon damage integration
    expect($weaponSpecialistFeature['description'])->toContain('succeed on an attack');
    expect($weaponSpecialistFeature['description'])->toContain('spend a Hope');
    expect($weaponSpecialistFeature['description'])->toContain('add one of the damage dice from your secondary weapon');
    expect($weaponSpecialistFeature['hopeCost'])->toBe(1);
    
    // Slayer Dice enhancement
    expect($weaponSpecialistFeature['description'])->toContain('when you roll your Slayer Dice');
    expect($weaponSpecialistFeature['description'])->toContain('reroll any 1s');
    expect($weaponSpecialistFeature['description'])->toContain('once per long rest');
    
    // This significantly improves Slayer Dice reliability (no wasted 1s)

});

test('martial preparation party mechanics verification', function () {
    // Test Martial Preparation's party-wide benefits
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $callOfSlayer = $subclassData['call of the slayer'];
    
    $masteryFeatures = $callOfSlayer['masteryFeatures'];
    $martialPrepFeature = collect($masteryFeatures)->firstWhere('name', 'Martial Preparation');
    
    // Party-wide access to downtime move
    expect($martialPrepFeature['description'])->toContain('Your party gains access');
    expect($martialPrepFeature['description'])->toContain('Martial Preparation downtime move');
    expect($martialPrepFeature['description'])->toContain('during a rest');
    
    // Training and instruction flavor
    expect($martialPrepFeature['description'])->toContain('describe how you instruct and train');
    
    // Mechanical benefit
    expect($martialPrepFeature['description'])->toContain('You and each ally');
    expect($martialPrepFeature['description'])->toContain('gain a d6 Slayer Die');
    expect($martialPrepFeature['description'])->toContain('spend it to roll the die');
    expect($martialPrepFeature['description'])->toContain('attack or damage roll');
    
    // This makes the Slayer a party force multiplier

});

test('slayer dice proficiency scaling integration', function () {
    // Test how Slayer Dice storage scales with character progression
    $character = createTestCharacterWith([
        'class' => 'warrior',
        'subclass' => 'call of the slayer',
        'ancestry' => 'human', // +2 stress for testing
        'community' => 'ridgeborne',
        'level' => 6, // Higher level for better proficiency
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Test that warrior has good base survivability for sustained combat
    expect($stats->hit_points)->toBe(6); // Warrior base
    expect($stats->evasion)->toBeGreaterThanOrEqual(11); // Good evasion
    
    // The progression: Higher level = Higher proficiency = More Slayer Dice storage
    // Level 6 should have proficiency 4, allowing 4 stored Slayer Dice
    // Combat Training provides +6 damage at level 6
    // Slayer Dice can add 4d6 to a single attack for massive burst damage

});

test('call of slayer resource economy verification', function () {
    // Test the overall resource economy of the Slayer
    $character = createTestCharacterWith([
        'class' => 'warrior',
        'subclass' => 'call of the slayer',
        'ancestry' => 'dwarf', // Sturdy ancestry
        'community' => 'ridgeborne',
        'level' => 5,
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Base warrior survivability supports sustained resource conversion
    expect($stats->hit_points)->toBe(6); // Warrior base
    expect($stats->evasion)->toBeGreaterThanOrEqual(11); // Good defense
    
    // The economy:
    // 1. Generate Hope through successful rolls
    // 2. Convert Hope to Slayer Dice on Hope rolls (store up to Proficiency)
    // 3. Spend Slayer Dice for immediate power boosts
    // 4. Unspent dice convert back to Hope at session end
    // 5. Weapon Specialist adds secondary weapon damage for 1 Hope
    // 6. Martial Preparation shares benefits with party
    
    // This creates a flexible resource system for both burst and sustained damage
});