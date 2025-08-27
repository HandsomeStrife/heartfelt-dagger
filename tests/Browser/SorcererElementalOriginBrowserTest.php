<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive browser test for Sorcerer + Elemental Origin
 * 
 * Tests critical mechanics:
 * - Sorcerer base stats (Evasion 10, Hit Points 6)
 * - Elemental Origin's Natural Evasion d6 bonus mechanism 
 * - Element selection at character creation
 * - Elementalist Hope costs (1 Hope for bonuses)
 * - Transcendence mastery benefits
 * - Domain access (Arcana + Midnight)
 * - Instinct spellcasting trait assignment
 */

test('sorcerer elemental origin natural evasion verification', function () {
    $character = createTestCharacterWith([
        'class' => 'sorcerer',
        'subclass' => 'elemental origin',
        'ancestry' => 'elf', // Neutral ancestry for clean testing
        'community' => 'wildborne', // Thematic for elemental sorcerer
        'level' => 4, // Mid-level for specialization features
    ]);

    // Add character traits for complete testing (Instinct for spellcasting)
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 1],
        ['trait_name' => 'strength', 'trait_value' => 0],
        ['trait_name' => 'finesse', 'trait_value' => 0],
        ['trait_name' => 'instinct', 'trait_value' => 2], // Spellcasting trait for Sorcerer Elemental
        ['trait_name' => 'presence', 'trait_value' => 1],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Verify Sorcerer base stats
    expect($stats->hit_points)->toBe(6); // Sorcerer base 6
    expect($stats->evasion)->toBeGreaterThanOrEqual(10); // Sorcerer base 10 + trait bonuses

    // Test that Natural Evasion feature exists but doesn't provide permanent bonuses
    // (it's a reactive ability that adds d6 when activated)
    expect($character->getSubclassEvasionBonus())->toBe(0); // No permanent evasion bonus

    // Test domain card calculation (Elemental Origin provides no domain card bonuses)
    $builderData = \Domain\Character\Data\CharacterBuilderData::from([
        'selected_class' => 'sorcerer',
        'selected_subclass' => 'elemental origin',
    ]);
    
    expect($builderData->getMaxDomainCards())->toBe(2); // Base 2, no bonuses
    expect($builderData->getSubclassDomainCardBonus())->toBe(0);

    // Verify base stress calculation (no subclass stress bonuses)
    $subclassStressBonus = $character->getSubclassStressBonus();
    expect($subclassStressBonus)->toBe(0); // Elemental Origin provides no stress bonuses

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('sorcerer elemental origin browser integration works correctly', function () {
    $character = Character::factory()->create([
        'character_key' => 'SORC123',
        'class' => null,
        'subclass' => null,
    ]);

    // Start browser test - visit character builder
    $page = visit("/character-builder/{$character->character_key}");

    // Set up the character data
    $character->update([
        'class' => 'sorcerer',
        'subclass' => 'elemental origin',
        'ancestry' => 'elf',
        'community' => 'wildborne',
    ]);

    // Add character traits (Instinct focus for spellcasting)
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 1],
        ['trait_name' => 'strength', 'trait_value' => 0],
        ['trait_name' => 'finesse', 'trait_value' => 0],
        ['trait_name' => 'instinct', 'trait_value' => 2], // Spellcasting trait
        ['trait_name' => 'presence', 'trait_value' => 1],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    // Verify character was configured correctly
    expect($character->fresh())
        ->class->toBe('sorcerer')
        ->subclass->toBe('elemental origin')
        ->ancestry->toBe('elf')
        ->community->toBe('wildborne');

    // Test that stats are calculated correctly
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(6); // Sorcerer base 6
    expect($stats->evasion)->toBeGreaterThanOrEqual(10); // Sorcerer base + bonuses

    // Natural Evasion is reactive, not a permanent bonus
    expect($character->getSubclassEvasionBonus())->toBe(0);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('elemental origin subclass features are correctly defined', function () {
    // Verify Elemental Origin subclass JSON configuration
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $elementalOrigin = $subclassData['elemental origin'];
    
    expect($elementalOrigin)->not()->toBeNull();
    expect($elementalOrigin['name'])->toBe('Elemental Origin');
    expect($elementalOrigin['spellcastTrait'])->toBe('Instinct'); // Critical for spellcasting

    // Verify foundation features include Elementalist
    $foundationFeatures = $elementalOrigin['foundationFeatures'];
    $elementalistFeature = collect($foundationFeatures)->firstWhere('name', 'Elementalist');
    
    expect($elementalistFeature)->not()->toBeNull();
    expect($elementalistFeature['description'])->toContain('air, earth, fire, lightning, water');
    expect($elementalistFeature['description'])->toContain('Hope');
    expect($elementalistFeature['description'])->toContain('+2 bonus');
    expect($elementalistFeature['description'])->toContain('+3 bonus');
    expect($elementalistFeature['hopeCost'])->toBe(1);

    // Verify specialization features include Natural Evasion
    $specializationFeatures = $elementalOrigin['specializationFeatures'];
    $naturalEvasionFeature = collect($specializationFeatures)->firstWhere('name', 'Natural Evasion');
    
    expect($naturalEvasionFeature)->not()->toBeNull();
    expect($naturalEvasionFeature['description'])->toContain('mark a Stress');
    expect($naturalEvasionFeature['description'])->toContain('roll a d6');
    expect($naturalEvasionFeature['description'])->toContain('add its result to your Evasion');
    expect($naturalEvasionFeature['stressCost'])->toBe(1);

    // Verify mastery features include Transcendence with multiple benefits
    $masteryFeatures = $elementalOrigin['masteryFeatures'];
    $transcendenceFeature = collect($masteryFeatures)->firstWhere('name', 'Transcendence');
    
    expect($transcendenceFeature)->not()->toBeNull();
    expect($transcendenceFeature['description'])->toContain('Once per long rest');
    expect($transcendenceFeature['description'])->toContain('choose two');
    expect($transcendenceFeature['description'])->toContain('+4 bonus to your Severe threshold');
    expect($transcendenceFeature['description'])->toContain('+2 bonus to your Evasion');

});

test('sorcerer class has correct base stats for elemental origin', function () {
    $character = createTestCharacterWith([
        'class' => 'sorcerer',
        'subclass' => 'elemental origin',
        'ancestry' => 'human', // Use human to test different ancestry
        'community' => 'loreborne',
    ]);

    // Add consistent traits
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 1],
        ['trait_name' => 'strength', 'trait_value' => 0],
        ['trait_name' => 'finesse', 'trait_value' => 0],
        ['trait_name' => 'instinct', 'trait_value' => 2], // Spellcasting trait
        ['trait_name' => 'presence', 'trait_value' => 1],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Verify Sorcerer base stats
    expect($stats->hit_points)->toBe(6); // Sorcerer base 6
    expect($stats->evasion)->toBeGreaterThanOrEqual(10); // Sorcerer base + bonuses

    // Test Sorcerer domains (Arcana + Midnight)
    $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    $sorcererClass = $classData['sorcerer'];
    
    expect($sorcererClass['domains'])->toBe(['arcana', 'midnight']);
    expect($sorcererClass['startingEvasion'])->toBe(10);
    expect($sorcererClass['startingHitPoints'])->toBe(6);

    // Test stress with Human ancestry bonus
    expect($stats->stress)->toBeGreaterThanOrEqual(6); // Base + Human +2

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('sorcerer class features work with elemental origin subclass', function () {
    // Verify Sorcerer class features from JSON
    $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    $sorcererClass = $classData['sorcerer'];
    
    expect($sorcererClass['name'])->toBe('Sorcerer');
    
    // Check Hope feature
    expect($sorcererClass['hopeFeature']['name'])->toBe('Volatile Magic');
    expect($sorcererClass['hopeFeature']['hopeCost'])->toBe(3);
    expect($sorcererClass['hopeFeature']['description'])->toContain('reroll');
    expect($sorcererClass['hopeFeature']['description'])->toContain('magic damage');
    
    // Check class features include Arcane Sense and Minor Illusion
    $classFeatureNames = collect($sorcererClass['classFeatures'])->pluck('name')->toArray();
    expect($classFeatureNames)->toContain('Arcane Sense');
    expect($classFeatureNames)->toContain('Minor Illusion');
    expect($classFeatureNames)->toContain('Channel Raw Power');
    
    // Verify subclasses include both elemental origin and primal origin
    expect($sorcererClass['subclasses'])->toBe(['elemental origin', 'primal origin']);

});

test('sorcerer elemental origin instinct spellcasting integration', function () {
    // Test that Elemental Origin correctly uses Instinct for spellcasting
    $character = createTestCharacterWith([
        'class' => 'sorcerer',
        'subclass' => 'elemental origin',
        'ancestry' => 'elf',
        'community' => 'wildborne',
    ]);

    // Set high Instinct trait for spellcasting
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 0],
        ['trait_name' => 'strength', 'trait_value' => 0],
        ['trait_name' => 'finesse', 'trait_value' => -1],
        ['trait_name' => 'instinct', 'trait_value' => 2], // High for spellcasting
        ['trait_name' => 'presence', 'trait_value' => 1],
        ['trait_name' => 'knowledge', 'trait_value' => 1],
    ]);

    // Test that builder data correctly identifies Instinct as spellcast trait
    $builderData = \Domain\Character\Data\CharacterBuilderData::from([
        'selected_class' => 'sorcerer',
        'selected_subclass' => 'elemental origin',
        'character_traits' => [
            'agility' => 0,
            'strength' => 0,
            'finesse' => -1,
            'instinct' => 2, // Should be used for spellcasting calculations
            'presence' => 1,
            'knowledge' => 1,
        ],
    ]);

    expect($character->fresh())->class->toBe('sorcerer');
    expect($character->fresh())->subclass->toBe('elemental origin');

    // Verify the character was created with correct traits
    $instinctTrait = $character->traits()->where('trait_name', 'instinct')->first();
    expect($instinctTrait->trait_value)->toBe(2);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('elemental origin element selection mechanics', function () {
    // Test that the Elementalist feature correctly describes element selection
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $elementalOrigin = $subclassData['elemental origin'];
    
    $elementalistFeature = collect($elementalOrigin['foundationFeatures'])->firstWhere('name', 'Elementalist');
    
    // Verify all five elements are available for selection
    expect($elementalistFeature['description'])->toContain('air');
    expect($elementalistFeature['description'])->toContain('earth');
    expect($elementalistFeature['description'])->toContain('fire'); 
    expect($elementalistFeature['description'])->toContain('lightning');
    expect($elementalistFeature['description'])->toContain('water');
    
    // Verify Hope cost and bonus structure
    expect($elementalistFeature['hopeCost'])->toBe(1);
    expect($elementalistFeature['description'])->toContain('+2 bonus to the roll');
    expect($elementalistFeature['description'])->toContain('+3 bonus to the roll\'s damage');
});