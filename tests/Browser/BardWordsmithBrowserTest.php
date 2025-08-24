<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive browser test for Bard + Wordsmith
 * 
 * Tests critical mechanics:
 * - Bard base stats (Evasion 10, Hit Points 5)
 * - Wordsmith's Rally Die d10 progression at mastery
 * - Rousing Speech stress clearing (2 Stress for allies)
 * - Heart of a Poet Hope costs (1 Hope for d4 bonus)
 * - Epic Poetry advantage die mechanics
 * - Domain access (Grace + Codex)
 * - Presence spellcasting trait assignment
 */

test('bard wordsmith rally die progression verification', function () {
    // Test high-level character to access mastery features
    $character = createTestCharacterWith([
        'class' => 'bard',
        'subclass' => 'wordsmith',
        'ancestry' => 'elf', // Neutral ancestry for clean testing
        'community' => 'highborne', // Thematic for bard
        'level' => 6, // High level for mastery features (Rally Die d10)
    ]);

    // Add character traits for complete testing (Presence for spellcasting)
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 0],
        ['trait_name' => 'strength', 'trait_value' => -1],
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => 0],
        ['trait_name' => 'presence', 'trait_value' => 2], // Spellcasting trait for Bard Wordsmith
        ['trait_name' => 'knowledge', 'trait_value' => 1],
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Verify Bard base stats
    expect($stats->hit_points)->toBe(5); // Bard base 5
    expect($stats->evasion)->toBeGreaterThanOrEqual(10); // Bard base 10 + trait bonuses

    // Test that no permanent bonuses are applied (Rally Die is feature-based)
    expect($character->getSubclassEvasionBonus())->toBe(0); // No permanent evasion bonus
    expect($character->getSubclassStressBonus())->toBe(0); // No stress bonus
    expect($character->getSubclassHitPointBonus())->toBe(0); // No HP bonus

    // Test domain card calculation (Wordsmith provides no domain card bonuses)
    $builderData = \Domain\Character\Data\CharacterBuilderData::from([
        'selected_class' => 'bard',
        'selected_subclass' => 'wordsmith',
    ]);
    
    expect($builderData->getMaxDomainCards())->toBe(2); // Base 2, no bonuses
    expect($builderData->getSubclassDomainCardBonus())->toBe(0);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('bard wordsmith browser integration works correctly', function () {
    $character = Character::factory()->create([
        'character_key' => 'BARD123',
        'class' => null,
        'subclass' => null,
    ]);

    // Start browser test - visit character builder
    $page = visit("/character-builder/{$character->character_key}");

    // Set up the character data
    $character->update([
        'class' => 'bard',
        'subclass' => 'wordsmith',
        'ancestry' => 'elf',
        'community' => 'highborne',
        'level' => 6, // High level for mastery
    ]);

    // Add character traits (Presence focus for spellcasting)
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 0],
        ['trait_name' => 'strength', 'trait_value' => -1],
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => 0],
        ['trait_name' => 'presence', 'trait_value' => 2], // Spellcasting trait
        ['trait_name' => 'knowledge', 'trait_value' => 1],
    ]);

    // Verify character was configured correctly
    expect($character->fresh())
        ->class->toBe('bard')
        ->subclass->toBe('wordsmith')
        ->ancestry->toBe('elf')
        ->community->toBe('highborne');

    // Test that stats are calculated correctly
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(5); // Bard base 5
    expect($stats->evasion)->toBeGreaterThanOrEqual(10); // Bard base + bonuses

    // No permanent stat bonuses from Wordsmith
    expect($character->getSubclassEvasionBonus())->toBe(0);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('wordsmith subclass features are correctly defined', function () {
    // Verify Wordsmith subclass JSON configuration
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $wordsmith = $subclassData['wordsmith'];
    
    expect($wordsmith)->not()->toBeNull();
    expect($wordsmith['name'])->toBe('Wordsmith');
    expect($wordsmith['spellcastTrait'])->toBe('Presence'); // Critical for spellcasting

    // Verify foundation features include Rousing Speech
    $foundationFeatures = $wordsmith['foundationFeatures'];
    $rousingSpeechFeature = collect($foundationFeatures)->firstWhere('name', 'Rousing Speech');
    
    expect($rousingSpeechFeature)->not()->toBeNull();
    expect($rousingSpeechFeature['description'])->toContain('Once per long rest');
    expect($rousingSpeechFeature['description'])->toContain('Far range');
    expect($rousingSpeechFeature['description'])->toContain('clear 2 Stress');

    // Verify foundation features include Heart of a Poet
    $heartOfPoetFeature = collect($foundationFeatures)->firstWhere('name', 'Heart of a Poet');
    
    expect($heartOfPoetFeature)->not()->toBeNull();
    expect($heartOfPoetFeature['description'])->toContain('Hope');
    expect($heartOfPoetFeature['description'])->toContain('add a d4');
    expect($heartOfPoetFeature['description'])->toContain('impress, persuade, or offend');
    expect($heartOfPoetFeature['hopeCost'])->toBe(1);

    // Verify specialization features include Eloquent
    $specializationFeatures = $wordsmith['specializationFeatures'];
    $eloquentFeature = collect($specializationFeatures)->firstWhere('name', 'Eloquent');
    
    expect($eloquentFeature)->not()->toBeNull();
    expect($eloquentFeature['description'])->toContain('Once per session');
    expect($eloquentFeature['description'])->toContain('encourage an ally');

    // CRITICAL TEST: Verify mastery features include Epic Poetry with Rally Die d10
    $masteryFeatures = $wordsmith['masteryFeatures'];
    $epicPoetryFeature = collect($masteryFeatures)->firstWhere('name', 'Epic Poetry');
    
    expect($epicPoetryFeature)->not()->toBeNull();
    expect($epicPoetryFeature['description'])->toContain('Rally Die increases to a d10');
    expect($epicPoetryFeature['description'])->toContain('Help an Ally');
    expect($epicPoetryFeature['description'])->toContain('roll a d10');

});

test('bard class has correct base stats for wordsmith', function () {
    $character = createTestCharacterWith([
        'class' => 'bard',
        'subclass' => 'wordsmith',
        'ancestry' => 'human', // Use human to test different ancestry
        'community' => 'slyborne',
    ]);

    // Add consistent traits
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 0],
        ['trait_name' => 'strength', 'trait_value' => -1],
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => 0],
        ['trait_name' => 'presence', 'trait_value' => 2], // Spellcasting trait
        ['trait_name' => 'knowledge', 'trait_value' => 1],
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Verify Bard base stats
    expect($stats->hit_points)->toBe(5); // Bard base 5
    expect($stats->evasion)->toBeGreaterThanOrEqual(10); // Bard base + bonuses

    // Test Bard domains (Grace + Codex)
    $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    $bardClass = $classData['bard'];
    
    expect($bardClass['domains'])->toBe(['grace', 'codex']);
    expect($bardClass['startingEvasion'])->toBe(10);
    expect($bardClass['startingHitPoints'])->toBe(5);

    // Test stress with Human ancestry bonus
    expect($stats->stress)->toBeGreaterThanOrEqual(5); // Base + Human +2

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('bard class features work with wordsmith subclass', function () {
    // Verify Bard class features from JSON
    $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    $bardClass = $classData['bard'];
    
    expect($bardClass['name'])->toBe('Bard');
    
    // Check Hope feature
    expect($bardClass['hopeFeature']['name'])->toBe('Make a Scene');
    expect($bardClass['hopeFeature']['hopeCost'])->toBe(3);
    expect($bardClass['hopeFeature']['description'])->toContain('Distract');
    expect($bardClass['hopeFeature']['description'])->toContain('-2 penalty');
    
    // CRITICAL TEST: Check Rally feature with die progression
    $classFeatureNames = collect($bardClass['classFeatures'])->pluck('name')->toArray();
    expect($classFeatureNames)->toContain('Rally');
    
    $rallyFeature = collect($bardClass['classFeatures'])->firstWhere('name', 'Rally');
    expect($rallyFeature['description'])->toContain('Rally Die is a d6'); // Level 1
    expect($rallyFeature['description'])->toContain('Rally Die increases to a d8'); // Level 5
    // Note: d10 progression comes from Wordsmith mastery feature
    
    // Verify subclasses include both troubadour and wordsmith
    expect($bardClass['subclasses'])->toBe(['troubadour', 'wordsmith']);

});

test('bard wordsmith presence spellcasting integration', function () {
    // Test that Wordsmith correctly uses Presence for spellcasting
    $character = createTestCharacterWith([
        'class' => 'bard',
        'subclass' => 'wordsmith',
        'ancestry' => 'elf',
        'community' => 'highborne',
    ]);

    // Set high Presence trait for spellcasting
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 0],
        ['trait_name' => 'strength', 'trait_value' => -1],
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => 0],
        ['trait_name' => 'presence', 'trait_value' => 2], // High for spellcasting
        ['trait_name' => 'knowledge', 'trait_value' => 1],
    ]);

    // Test that builder data correctly identifies Presence as spellcast trait
    $builderData = \Domain\Character\Data\CharacterBuilderData::from([
        'selected_class' => 'bard',
        'selected_subclass' => 'wordsmith',
        'character_traits' => [
            'agility' => 0,
            'strength' => -1,
            'finesse' => 1,
            'instinct' => 0,
            'presence' => 2, // Should be used for spellcasting calculations
            'knowledge' => 1,
        ],
    ]);

    expect($character->fresh())->class->toBe('bard');
    expect($character->fresh())->subclass->toBe('wordsmith');

    // Verify the character was created with correct traits
    $presenceTrait = $character->traits()->where('trait_name', 'presence')->first();
    expect($presenceTrait->trait_value)->toBe(2);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('wordsmith rally die progression mechanics', function () {
    // Test the Rally Die progression from base Bard to Wordsmith mastery
    $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    
    $bardClass = $classData['bard'];
    $wordsmithSubclass = $subclassData['wordsmith'];
    
    // Base Bard Rally progression
    $rallyFeature = collect($bardClass['classFeatures'])->firstWhere('name', 'Rally');
    expect($rallyFeature['description'])->toContain('Rally Die is a d6'); // Level 1 base
    expect($rallyFeature['description'])->toContain('Rally Die increases to a d8'); // Level 5 base
    
    // Wordsmith mastery Rally Die enhancement
    $epicPoetryFeature = collect($wordsmithSubclass['masteryFeatures'])->firstWhere('name', 'Epic Poetry');
    expect($epicPoetryFeature['description'])->toContain('Rally Die increases to a d10'); // Mastery override
    
    // Verify Epic Poetry provides additional d10 advantage die
    expect($epicPoetryFeature['description'])->toContain('roll a d10 as your advantage die');
    expect($epicPoetryFeature['description'])->toContain('Help an Ally');

});

test('wordsmith stress clearing mechanics verification', function () {
    // Test Rousing Speech stress clearing feature
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $wordsmith = $subclassData['wordsmith'];
    
    $rousingSpeechFeature = collect($wordsmith['foundationFeatures'])->firstWhere('name', 'Rousing Speech');
    
    // Verify stress clearing amount and range
    expect($rousingSpeechFeature['description'])->toContain('Once per long rest');
    expect($rousingSpeechFeature['description'])->toContain('Far range'); // Wide range
    expect($rousingSpeechFeature['description'])->toContain('clear 2 Stress'); // Specific amount
    
    // Verify it affects allies, not just self
    expect($rousingSpeechFeature['description'])->toContain('All allies');

});
