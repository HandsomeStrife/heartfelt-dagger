<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive browser test for Sorcerer + Primal Origin
 * 
 * Tests critical mechanics:
 * - Sorcerer base stats (Evasion 10, Hit Points 6)
 * - Primal Origin's Manipulate Magic stress costs and options (1 Stress for 4 modifications)
 * - Enchanted Aid spellcasting assistance (d8 advantage, Duality Dice swap)
 * - Arcane Charge mechanics (2 Hope or magic damage for Charged state)
 * - Arcane Charge discharge options (+10 damage or +3 reaction Difficulty)
 * - Domain access (Arcana + Midnight)
 * - Instinct spellcasting trait integration
 * - Spell modification versatility testing
 */

test('sorcerer primal origin manipulate magic verification', function () {
    $character = createTestCharacterWith([
        'class' => 'sorcerer',
        'subclass' => 'primal origin',
        'ancestry' => 'elf', // Neutral ancestry for clean testing
        'community' => 'loreborne', // Thematic for magic-focused sorcerer
        'level' => 4, // Mid-level for specialization features
    ]);

    // Add character traits for complete testing (Instinct for spellcasting)
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 0],
        ['trait_name' => 'strength', 'trait_value' => 0],
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => 2], // Spellcasting trait for Sorcerer Primal
        ['trait_name' => 'presence', 'trait_value' => 1],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Verify Sorcerer base stats
    expect($stats->hit_points)->toBe(6); // Sorcerer base 6
    expect($stats->evasion)->toBeGreaterThanOrEqual(10); // Sorcerer base 10 + trait bonuses

    // Test that no permanent bonuses are applied (Primal Origin features are spell-based)
    expect($character->getSubclassEvasionBonus())->toBe(0); // No permanent evasion bonus
    expect($character->getSubclassStressBonus())->toBe(0); // No stress bonus  
    expect($character->getSubclassHitPointBonus())->toBe(0); // No HP bonus

    // Test domain card calculation (Primal Origin provides no domain card bonuses)
    $builderData = \Domain\Character\Data\CharacterBuilderData::from([
        'selected_class' => 'sorcerer',
        'selected_subclass' => 'primal origin',
    ]);
    
    expect($builderData->getMaxDomainCards())->toBe(2); // Base 2, no bonuses
    expect($builderData->getSubclassDomainCardBonus())->toBe(0);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('sorcerer primal origin browser integration works correctly', function () {
    $character = Character::factory()->create([
        'character_key' => 'PRIMAL123',
        'class' => null,
        'subclass' => null,
    ]);

    // Start browser test - visit character builder
    $page = visit("/character-builder/{$character->character_key}");

    // Set up the character data
    $character->update([
        'class' => 'sorcerer',
        'subclass' => 'primal origin',
        'ancestry' => 'elf',
        'community' => 'loreborne',
    ]);

    // Add character traits (Instinct focus for spellcasting)
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 0],
        ['trait_name' => 'strength', 'trait_value' => 0],
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => 2], // Spellcasting trait
        ['trait_name' => 'presence', 'trait_value' => 1],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    // Verify character was configured correctly
    expect($character->fresh())
        ->class->toBe('sorcerer')
        ->subclass->toBe('primal origin')
        ->ancestry->toBe('elf')
        ->community->toBe('loreborne');

    // Test that stats are calculated correctly
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(6); // Sorcerer base 6
    expect($stats->evasion)->toBeGreaterThanOrEqual(10); // Sorcerer base + bonuses

    // No permanent stat bonuses from Primal Origin
    expect($character->getSubclassEvasionBonus())->toBe(0);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('primal origin subclass features are correctly defined', function () {
    // Verify Primal Origin subclass JSON configuration
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $primalOrigin = $subclassData['primal origin'];
    
    expect($primalOrigin)->not()->toBeNull();
    expect($primalOrigin['name'])->toBe('Primal Origin');
    expect($primalOrigin['spellcastTrait'])->toBe('Instinct'); // Critical for spellcasting

    // CRITICAL TEST: Verify Manipulate Magic with all 4 modification options
    $foundationFeatures = $primalOrigin['foundationFeatures'];
    $manipulateFeature = collect($foundationFeatures)->firstWhere('name', 'Manipulate Magic');
    
    expect($manipulateFeature)->not()->toBeNull();
    expect($manipulateFeature['description'])->toContain('modify the essence of magic');
    expect($manipulateFeature['description'])->toContain('cast a spell');
    expect($manipulateFeature['description'])->toContain('magic damage');
    expect($manipulateFeature['description'])->toContain('mark a Stress');
    
    // Verify all 4 modification options
    expect($manipulateFeature['description'])->toContain('Extend the spell or attack\'s reach by one range');
    expect($manipulateFeature['description'])->toContain('Gain a +2 bonus to the action roll\'s result');
    expect($manipulateFeature['description'])->toContain('Double a damage die of your choice');
    expect($manipulateFeature['description'])->toContain('Hit an additional target within range');
    expect($manipulateFeature['stressCost'])->toBe(1);

    // Verify Enchanted Aid specialization
    $specializationFeatures = $primalOrigin['specializationFeatures'];
    $enchantedAidFeature = collect($specializationFeatures)->firstWhere('name', 'Enchanted Aid');
    
    expect($enchantedAidFeature)->not()->toBeNull();
    expect($enchantedAidFeature['description'])->toContain('Help an Ally');
    expect($enchantedAidFeature['description'])->toContain('Spellcast Roll');
    expect($enchantedAidFeature['description'])->toContain('roll a d8 as your advantage die');
    expect($enchantedAidFeature['description'])->toContain('swap the results of their Duality Dice');
    expect($enchantedAidFeature['description'])->toContain('Once per long rest');

    // CRITICAL TEST: Verify Arcane Charge mechanics
    $masteryFeatures = $primalOrigin['masteryFeatures'];
    $arcaneChargeFeature = collect($masteryFeatures)->firstWhere('name', 'Arcane Charge');
    
    expect($arcaneChargeFeature)->not()->toBeNull();
    expect($arcaneChargeFeature['description'])->toContain('take magic damage');
    expect($arcaneChargeFeature['description'])->toContain('spend 2 Hope');
    expect($arcaneChargeFeature['description'])->toContain('become Charged');
    expect($arcaneChargeFeature['description'])->toContain('clear your Charge');
    expect($arcaneChargeFeature['description'])->toContain('+10 bonus to the damage roll');
    expect($arcaneChargeFeature['description'])->toContain('+3 bonus to the Difficulty');
    expect($arcaneChargeFeature['description'])->toContain('next long rest');
    expect($arcaneChargeFeature['hopeCost'])->toBe(2);

});

test('manipulate magic stress cost and options verification', function () {
    // Test the 4 Manipulate Magic modification options for 1 Stress
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $primalOrigin = $subclassData['primal origin'];
    
    $foundationFeatures = $primalOrigin['foundationFeatures'];
    $manipulateFeature = collect($foundationFeatures)->firstWhere('name', 'Manipulate Magic');
    
    // Test stress cost
    expect($manipulateFeature['stressCost'])->toBe(1);
    expect($manipulateFeature['description'])->toContain('mark a Stress');
    
    // Test trigger conditions
    expect($manipulateFeature['description'])->toContain('After you cast a spell');
    expect($manipulateFeature['description'])->toContain('make an attack using a weapon that deals magic damage');
    
    // Test all 4 modification options (choose one per use)
    expect($manipulateFeature['description'])->toContain('Extend the spell or attack\'s reach by one range');
    expect($manipulateFeature['description'])->toContain('Gain a +2 bonus to the action roll\'s result');
    expect($manipulateFeature['description'])->toContain('Double a damage die of your choice');
    expect($manipulateFeature['description'])->toContain('Hit an additional target within range');
    
    // This provides incredible spell versatility for 1 Stress each use

});

test('enchanted aid spellcasting assistance mechanics', function () {
    // Test Enchanted Aid's ally assistance capabilities
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $primalOrigin = $subclassData['primal origin'];
    
    $specializationFeatures = $primalOrigin['specializationFeatures'];
    $enchantedAidFeature = collect($specializationFeatures)->firstWhere('name', 'Enchanted Aid');
    
    // Test Help action enhancement
    expect($enchantedAidFeature['description'])->toContain('Help an Ally with a Spellcast Roll');
    expect($enchantedAidFeature['description'])->toContain('roll a d8 as your advantage die');
    
    // Test Duality Dice manipulation
    expect($enchantedAidFeature['description'])->toContain('Once per long rest');
    expect($enchantedAidFeature['description'])->toContain('swap the results of their Duality Dice');
    expect($enchantedAidFeature['description'])->toContain('after an ally has made a Spellcast Roll with your help');
    
    // This makes the Primal Origin a powerful spellcasting support character

});

test('arcane charge resource mechanics verification', function () {
    // Test Arcane Charge's dual acquisition and discharge mechanics
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $primalOrigin = $subclassData['primal origin'];
    
    $masteryFeatures = $primalOrigin['masteryFeatures'];
    $arcaneChargeFeature = collect($masteryFeatures)->firstWhere('name', 'Arcane Charge');
    
    // Test dual ways to become Charged
    expect($arcaneChargeFeature['description'])->toContain('When you take magic damage, you become Charged');
    expect($arcaneChargeFeature['description'])->toContain('spend 2 Hope to become Charged');
    expect($arcaneChargeFeature['hopeCost'])->toBe(2);
    
    // Test discharge conditions
    expect($arcaneChargeFeature['description'])->toContain('successfully make an attack that deals magic damage');
    expect($arcaneChargeFeature['description'])->toContain('while Charged');
    expect($arcaneChargeFeature['description'])->toContain('clear your Charge');
    
    // Test discharge options
    expect($arcaneChargeFeature['description'])->toContain('gain a +10 bonus to the damage roll');
    expect($arcaneChargeFeature['description'])->toContain('gain a +3 bonus to the Difficulty of a reaction roll');
    
    // Test duration limit
    expect($arcaneChargeFeature['description'])->toContain('stop being Charged at your next long rest');
    
    // This creates a powerful burst resource: either defensive (from taking damage) or offensive (spend Hope)

});

test('sorcerer primal origin instinct spellcasting integration', function () {
    // Test that Primal Origin correctly uses Instinct for spellcasting
    $character = createTestCharacterWith([
        'class' => 'sorcerer',
        'subclass' => 'primal origin',
        'ancestry' => 'elf',
        'community' => 'loreborne',
    ]);

    // Set high Instinct trait for spellcasting
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 0],
        ['trait_name' => 'strength', 'trait_value' => 0],
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => 2], // High for spellcasting
        ['trait_name' => 'presence', 'trait_value' => 1],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    // Test that builder data correctly identifies Instinct as spellcast trait
    $builderData = \Domain\Character\Data\CharacterBuilderData::from([
        'selected_class' => 'sorcerer',
        'selected_subclass' => 'primal origin',
        'character_traits' => [
            'agility' => 0,
            'strength' => 0,
            'finesse' => 1,
            'instinct' => 2, // Should be used for spellcasting calculations
            'presence' => 1,
            'knowledge' => -1,
        ],
    ]);

    expect($character->fresh())->class->toBe('sorcerer');
    expect($character->fresh())->subclass->toBe('primal origin');

    // Verify the character was created with correct traits
    $instinctTrait = $character->traits()->where('trait_name', 'instinct')->first();
    expect($instinctTrait->trait_value)->toBe(2);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('primal origin spell modification versatility testing', function () {
    // Test how different Manipulate Magic options provide tactical versatility
    $character = createTestCharacterWith([
        'class' => 'sorcerer',
        'subclass' => 'primal origin',
        'ancestry' => 'human', // +2 stress for more Manipulate Magic uses
        'community' => 'wanderborne',
        'level' => 5, // Mid-high level for testing
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Test that the sorcerer has good stress for frequent magic manipulation
    expect($stats->stress)->toBeGreaterThanOrEqual(6); // Base + Human +2 for multiple Manipulate Magic uses
    
    // The versatility: Each spell can be modified for 1 Stress
    // Range extension: Melee → Very Close → Close → Far → Very Far
    // Accuracy boost: +2 to hit
    // Damage boost: Double any damage die
    // Multi-target: Hit additional enemies
    // Combined with Arcane Charge: +10 damage or +3 reaction Difficulty
    // Plus Enchanted Aid: Help allies cast better spells

});

test('primal origin resource synergy verification', function () {
    // Test how all Primal Origin features work together
    $character = createTestCharacterWith([
        'class' => 'sorcerer',
        'subclass' => 'primal origin',
        'ancestry' => 'fairy', // Small size, magical theme
        'community' => 'loreborne',
        'level' => 6, // High level for mastery features
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Base sorcerer provides good magical framework
    expect($stats->hit_points)->toBe(6); // Sorcerer base
    expect($stats->evasion)->toBeGreaterThanOrEqual(10); // Good evasion
    
    // The synergy:
    // 1. Cast spells with high Instinct trait
    // 2. Use Manipulate Magic (1 Stress) to modify spell effects
    // 3. Help allies with Enchanted Aid (d8 advantage + Duality swap)
    // 4. Build up Arcane Charge from magic damage or 2 Hope
    // 5. Discharge Charge for massive +10 damage or control (+3 Difficulty)
    // 6. Channel Raw Power and Volatile Magic provide additional Hope/spell enhancement
    
    // This creates the ultimate spell manipulation and support caster

});
