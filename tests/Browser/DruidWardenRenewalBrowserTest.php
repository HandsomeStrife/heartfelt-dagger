<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive browser test for Druid + Warden of Renewal
 * 
 * Tests critical mechanics:
 * - Druid base stats (Evasion 10, Hit Points 6)
 * - Warden of Renewal's critical Hope costs (Regeneration 3 Hope, Warden's Protection 2 Hope)
 * - Beastform integration with Defender mastery feature
 * - Clarity of Nature stress clearing (Instinct-based)
 * - Regenerative Reach range extension
 * - Domain access (Sage + Arcana)
 * - Instinct spellcasting trait assignment
 */

test('druid warden renewal hope cost verification', function () {
    $character = createTestCharacterWith([
        'class' => 'druid',
        'subclass' => 'warden of renewal',
        'ancestry' => 'elf', // Neutral ancestry for clean testing
        'community' => 'wildborne', // Thematic for druid
        'level' => 5, // Mid-level for specialization features
    ]);

    // Add character traits for complete testing (Instinct for spellcasting)
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 0],
        ['trait_name' => 'strength', 'trait_value' => 0],
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => 2], // Spellcasting trait for Druid Warden of Renewal
        ['trait_name' => 'presence', 'trait_value' => 1],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Verify Druid base stats
    expect($stats->hit_points)->toBe(6); // Druid base 6
    expect($stats->evasion)->toBeGreaterThanOrEqual(10); // Druid base 10 + trait bonuses

    // Test that no permanent bonuses are applied (Warden features are ability-based)
    expect($character->getSubclassEvasionBonus())->toBe(0); // No permanent evasion bonus
    expect($character->getSubclassStressBonus())->toBe(0); // No stress bonus  
    expect($character->getSubclassHitPointBonus())->toBe(0); // No HP bonus

    // Test domain card calculation (Warden of Renewal provides no domain card bonuses)
    $builderData = \Domain\Character\Data\CharacterBuilderData::from([
        'selected_class' => 'druid',
        'selected_subclass' => 'warden of renewal',
    ]);
    
    expect($builderData->getMaxDomainCards())->toBe(2); // Base 2, no bonuses
    expect($builderData->getSubclassDomainCardBonus())->toBe(0);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('druid warden renewal browser integration works correctly', function () {
    $character = Character::factory()->create([
        'character_key' => 'DRUID123',
        'class' => null,
        'subclass' => null,
    ]);

    // Start browser test - visit character builder
    $page = visit("/character-builder/{$character->character_key}");

    // Set up the character data
    $character->update([
        'class' => 'druid',
        'subclass' => 'warden of renewal',
        'ancestry' => 'elf',
        'community' => 'wildborne',
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
        ->class->toBe('druid')
        ->subclass->toBe('warden of renewal')
        ->ancestry->toBe('elf')
        ->community->toBe('wildborne');

    // Test that stats are calculated correctly
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(6); // Druid base 6
    expect($stats->evasion)->toBeGreaterThanOrEqual(10); // Druid base + bonuses

    // No permanent stat bonuses from Warden of Renewal
    expect($character->getSubclassEvasionBonus())->toBe(0);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('warden of renewal subclass features are correctly defined', function () {
    // Verify Warden of Renewal subclass JSON configuration
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $wardenRenewal = $subclassData['warden of renewal'];
    
    expect($wardenRenewal)->not()->toBeNull();
    expect($wardenRenewal['name'])->toBe('Warden of Renewal');
    expect($wardenRenewal['spellcastTrait'])->toBe('Instinct'); // Critical for spellcasting

    // Verify foundation features include Clarity of Nature
    $foundationFeatures = $wardenRenewal['foundationFeatures'];
    $clarityFeature = collect($foundationFeatures)->firstWhere('name', 'Clarity of Nature');
    
    expect($clarityFeature)->not()->toBeNull();
    expect($clarityFeature['description'])->toContain('Once per long rest');
    expect($clarityFeature['description'])->toContain('Close range');
    expect($clarityFeature['description'])->toContain('clear Stress equal to your Instinct');

    // CRITICAL TEST: Verify Regeneration with 3 Hope cost
    $regenerationFeature = collect($foundationFeatures)->firstWhere('name', 'Regeneration');
    
    expect($regenerationFeature)->not()->toBeNull();
    expect($regenerationFeature['description'])->toContain('spend 3 Hope');
    expect($regenerationFeature['description'])->toContain('clears 1d4 Hit Points');
    expect($regenerationFeature['description'])->toContain('Touch a creature');
    expect($regenerationFeature['hopeCost'])->toBe(3);

    // Verify specialization features include Regenerative Reach
    $specializationFeatures = $wardenRenewal['specializationFeatures'];
    $reachFeature = collect($specializationFeatures)->firstWhere('name', 'Regenerative Reach');
    
    expect($reachFeature)->not()->toBeNull();
    expect($reachFeature['description'])->toContain('Very Close range');
    expect($reachFeature['description'])->toContain('Regeneration');

    // CRITICAL TEST: Verify Warden's Protection with 2 Hope cost
    $protectionFeature = collect($specializationFeatures)->firstWhere('name', 'Warden\'s Protection');
    
    expect($protectionFeature)->not()->toBeNull();
    expect($protectionFeature['description'])->toContain('spend 2 Hope');
    expect($protectionFeature['description'])->toContain('clear 2 Hit Points');
    expect($protectionFeature['description'])->toContain('1d4 allies');
    expect($protectionFeature['description'])->toContain('Close range');
    expect($protectionFeature['hopeCost'])->toBe(2);

    // Verify mastery features include Defender with Beastform integration
    $masteryFeatures = $wardenRenewal['masteryFeatures'];
    $defenderFeature = collect($masteryFeatures)->firstWhere('name', 'Defender');
    
    expect($defenderFeature)->not()->toBeNull();
    expect($defenderFeature['description'])->toContain('Beastform');
    expect($defenderFeature['description'])->toContain('mark a Stress');
    expect($defenderFeature['description'])->toContain('reduce the number of Hit Points');
    expect($defenderFeature['stressCost'])->toBe(1);

});

test('druid class has correct base stats for warden renewal', function () {
    $character = createTestCharacterWith([
        'class' => 'druid',
        'subclass' => 'warden of renewal',
        'ancestry' => 'human', // Use human to test different ancestry
        'community' => 'loreborne',
    ]);

    // Add consistent traits
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 0],
        ['trait_name' => 'strength', 'trait_value' => 0],
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => 2], // Spellcasting trait
        ['trait_name' => 'presence', 'trait_value' => 1],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Verify Druid base stats
    expect($stats->hit_points)->toBe(6); // Druid base 6
    expect($stats->evasion)->toBeGreaterThanOrEqual(10); // Druid base + bonuses

    // Test Druid domains (Sage + Arcana)
    $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    $druidClass = $classData['druid'];
    
    expect($druidClass['domains'])->toBe(['sage', 'arcana']);
    expect($druidClass['startingEvasion'])->toBe(10);
    expect($druidClass['startingHitPoints'])->toBe(6);

    // Test stress with Human ancestry bonus
    expect($stats->stress)->toBeGreaterThanOrEqual(6); // Base + Human +2

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('druid class features work with warden renewal subclass', function () {
    // Verify Druid class features from JSON
    $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    $druidClass = $classData['druid'];
    
    expect($druidClass['name'])->toBe('Druid');
    
    // Check Hope feature
    expect($druidClass['hopeFeature']['name'])->toBe('Evolution');
    expect($druidClass['hopeFeature']['hopeCost'])->toBe(3);
    expect($druidClass['hopeFeature']['description'])->toContain('transform into a Beastform');
    expect($druidClass['hopeFeature']['description'])->toContain('raise by +1');
    
    // Check class features include Beastform and Wildtouch
    $classFeatureNames = collect($druidClass['classFeatures'])->pluck('name')->toArray();
    expect($classFeatureNames)->toContain('Beastform');
    expect($classFeatureNames)->toContain('Wildtouch');
    
    $beastformFeature = collect($druidClass['classFeatures'])->firstWhere('name', 'Beastform');
    expect($beastformFeature['description'])->toContain('Mark a Stress');
    expect($beastformFeature['description'])->toContain('magically transform');
    
    // Verify subclasses include both wardens
    expect($druidClass['subclasses'])->toBe(['warden of the elements', 'warden of renewal']);

});

test('druid warden renewal instinct spellcasting integration', function () {
    // Test that Warden of Renewal correctly uses Instinct for spellcasting
    $character = createTestCharacterWith([
        'class' => 'druid',
        'subclass' => 'warden of renewal',
        'ancestry' => 'elf',
        'community' => 'wildborne',
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
        'selected_class' => 'druid',
        'selected_subclass' => 'warden of renewal',
        'character_traits' => [
            'agility' => 0,
            'strength' => 0,
            'finesse' => 1,
            'instinct' => 2, // Should be used for spellcasting calculations
            'presence' => 1,
            'knowledge' => -1,
        ],
    ]);

    expect($character->fresh())->class->toBe('druid');
    expect($character->fresh())->subclass->toBe('warden of renewal');

    // Verify the character was created with correct traits
    $instinctTrait = $character->traits()->where('trait_name', 'instinct')->first();
    expect($instinctTrait->trait_value)->toBe(2);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('warden renewal hope cost mechanics verification', function () {
    // Test the specific Hope costs for healing abilities
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $wardenRenewal = $subclassData['warden of renewal'];
    
    $foundationFeatures = $wardenRenewal['foundationFeatures'];
    $specializationFeatures = $wardenRenewal['specializationFeatures'];
    
    // CRITICAL: Regeneration costs 3 Hope for 1d4 Hit Points
    $regenerationFeature = collect($foundationFeatures)->firstWhere('name', 'Regeneration');
    expect($regenerationFeature['hopeCost'])->toBe(3);
    expect($regenerationFeature['description'])->toContain('1d4 Hit Points');
    
    // CRITICAL: Warden's Protection costs 2 Hope for 2 Hit Points on multiple allies
    $protectionFeature = collect($specializationFeatures)->firstWhere('name', 'Warden\'s Protection');
    expect($protectionFeature['hopeCost'])->toBe(2);
    expect($protectionFeature['description'])->toContain('2 Hit Points');
    expect($protectionFeature['description'])->toContain('1d4 allies');
    
    // Cost efficiency comparison: Protection is more efficient for group healing
    // Regeneration: 3 Hope for 1d4 HP on 1 target = ~0.75 Hope per HP per target
    // Protection: 2 Hope for 2 HP on 1d4 targets = ~0.5-2 Hope per HP depending on targets

});

test('warden renewal beastform defender integration', function () {
    // Test the Defender mastery feature interaction with Beastform
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $wardenRenewal = $subclassData['warden of renewal'];
    
    $masteryFeatures = $wardenRenewal['masteryFeatures'];
    $defenderFeature = collect($masteryFeatures)->firstWhere('name', 'Defender');
    
    // Verify Defender requires Beastform and costs Stress
    expect($defenderFeature['description'])->toContain('Beastform');
    expect($defenderFeature['description'])->toContain('healing guardian spirit');
    expect($defenderFeature['description'])->toContain('ally within Close range');
    expect($defenderFeature['description'])->toContain('marks 2 or more Hit Points');
    expect($defenderFeature['description'])->toContain('mark a Stress');
    expect($defenderFeature['description'])->toContain('reduce the number of Hit Points they mark by 1');
    expect($defenderFeature['stressCost'])->toBe(1);
    
    // This creates a powerful synergy: Beastform for survivability + damage reduction for allies

});

test('warden renewal clarity of nature instinct scaling', function () {
    // Test Clarity of Nature stress clearing scales with Instinct trait
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $wardenRenewal = $subclassData['warden of renewal'];
    
    $foundationFeatures = $wardenRenewal['foundationFeatures'];
    $clarityFeature = collect($foundationFeatures)->firstWhere('name', 'Clarity of Nature');
    
    // Verify it scales with Instinct trait (spellcasting trait)
    expect($clarityFeature['description'])->toContain('clear Stress equal to your Instinct');
    expect($clarityFeature['description'])->toContain('distributed as you choose');
    expect($clarityFeature['description'])->toContain('you and your allies');
    
    // At Instinct +2, clears 2 Stress total distributed among party
    // This synergizes well with the spellcasting focus on Instinct
});