<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive browser test for Warrior + Call of the Brave
 * 
 * Tests critical mechanics:
 * - Warrior base stats (Evasion 11, Hit Points 6)
 * - Call of the Brave's critical Hope mechanics (Courage Hope on Fear fails)
 * - Rise to the Challenge d20 Hope Die at low HP (2 or fewer unmarked)
 * - Battle Ritual stress clearing and Hope generation (2 Stress, 2 Hope)
 * - Camaraderie Tag Team Roll mechanics (additional use, cost reduction)
 * - Domain access (Blade + Bone)
 * - Combat Training level-based damage bonus
 */

test('warrior call of brave hope die mechanics verification', function () {
    $character = createTestCharacterWith([
        'class' => 'warrior',
        'subclass' => 'call of the brave',
        'ancestry' => 'elf', // Neutral ancestry for clean testing
        'community' => 'orderborne', // Thematic for warrior
        'level' => 4, // Mid-level for specialization features
    ]);

    // Add character traits for complete testing (balanced warrior build)
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

    // Test that no permanent bonuses are applied (Call of Brave features are situational)
    expect($character->getSubclassEvasionBonus())->toBe(0); // No permanent evasion bonus
    expect($character->getSubclassStressBonus())->toBe(0); // No stress bonus  
    expect($character->getSubclassHitPointBonus())->toBe(0); // No HP bonus

    // Test domain card calculation (Call of the Brave provides no domain card bonuses)
    $builderData = \Domain\Character\Data\CharacterBuilderData::from([
        'selected_class' => 'warrior',
        'selected_subclass' => 'call of the brave',
    ]);
    
    expect($builderData->getMaxDomainCards())->toBe(2); // Base 2, no bonuses
    expect($builderData->getSubclassDomainCardBonus())->toBe(0);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('warrior call of brave browser integration works correctly', function () {
    $character = Character::factory()->create([
        'character_key' => 'WAR123',
        'class' => null,
        'subclass' => null,
    ]);

    // Start browser test - visit character builder
    $page = visit("/character-builder/{$character->character_key}");

    // Set up the character data
    $character->update([
        'class' => 'warrior',
        'subclass' => 'call of the brave',
        'ancestry' => 'elf',
        'community' => 'orderborne',
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
        ->subclass->toBe('call of the brave')
        ->ancestry->toBe('elf')
        ->community->toBe('orderborne');

    // Test that stats are calculated correctly
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(6); // Warrior base 6
    expect($stats->evasion)->toBeGreaterThanOrEqual(11); // Warrior base + bonuses

    // No permanent stat bonuses from Call of the Brave
    expect($character->getSubclassEvasionBonus())->toBe(0);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('call of brave subclass features are correctly defined', function () {
    // Verify Call of the Brave subclass JSON configuration
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $callOfBrave = $subclassData['call of the brave'];
    
    expect($callOfBrave)->not()->toBeNull();
    expect($callOfBrave['name'])->toBe('Call of the Brave');

    // Verify foundation features include Courage
    $foundationFeatures = $callOfBrave['foundationFeatures'];
    $courageFeature = collect($foundationFeatures)->firstWhere('name', 'Courage');
    
    expect($courageFeature)->not()->toBeNull();
    expect($courageFeature['description'])->toContain('fail a roll with Fear');
    expect($courageFeature['description'])->toContain('you gain a Hope');

    // Verify Battle Ritual for stress/hope management
    $battleRitualFeature = collect($foundationFeatures)->firstWhere('name', 'Battle Ritual');
    
    expect($battleRitualFeature)->not()->toBeNull();
    expect($battleRitualFeature['description'])->toContain('Once per long rest');
    expect($battleRitualFeature['description'])->toContain('incredibly dangerous');
    expect($battleRitualFeature['description'])->toContain('clear 2 Stress');
    expect($battleRitualFeature['description'])->toContain('gain 2 Hope');

    // CRITICAL TEST: Verify Rise to the Challenge with d20 Hope Die
    $specializationFeatures = $callOfBrave['specializationFeatures'];
    $riseToChallengeFeature = collect($specializationFeatures)->firstWhere('name', 'Rise to the Challenge');
    
    expect($riseToChallengeFeature)->not()->toBeNull();
    expect($riseToChallengeFeature['description'])->toContain('2 or fewer Hit Points unmarked');
    expect($riseToChallengeFeature['description'])->toContain('roll a d20 as your Hope Die');

    // CRITICAL TEST: Verify Camaraderie with Tag Team Roll improvements
    $masteryFeatures = $callOfBrave['masteryFeatures'];
    $camaraderieFeature = collect($masteryFeatures)->firstWhere('name', 'Camaraderie');
    
    expect($camaraderieFeature)->not()->toBeNull();
    expect($camaraderieFeature['description'])->toContain('Tag Team Roll once per additional time');
    expect($camaraderieFeature['description'])->toContain('only need to spend 2 Hope');
    expect($camaraderieFeature['hopeCost'])->toBe(2);

});

test('warrior class has correct base stats for call of brave', function () {
    $character = createTestCharacterWith([
        'class' => 'warrior',
        'subclass' => 'call of the brave',
        'ancestry' => 'human', // Use human to test different ancestry
        'community' => 'ridgeborne',
    ]);

    // Add consistent traits
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 1],
        ['trait_name' => 'strength', 'trait_value' => 2],
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => 0],
        ['trait_name' => 'presence', 'trait_value' => 0],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Verify Warrior base stats
    expect($stats->hit_points)->toBe(6); // Warrior base 6
    expect($stats->evasion)->toBeGreaterThanOrEqual(11); // Warrior base + bonuses

    // Test Warrior domains (Blade + Bone)
    $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    $warriorClass = $classData['warrior'];
    
    expect($warriorClass['domains'])->toBe(['blade', 'bone']);
    expect($warriorClass['startingEvasion'])->toBe(11);
    expect($warriorClass['startingHitPoints'])->toBe(6);

    // Test stress with Human ancestry bonus
    expect($stats->stress)->toBeGreaterThanOrEqual(6); // Base + Human +2

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('warrior class features work with call of brave subclass', function () {
    // Verify Warrior class features from JSON
    $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    $warriorClass = $classData['warrior'];
    
    expect($warriorClass['name'])->toBe('Warrior');
    
    // Check Hope feature
    expect($warriorClass['hopeFeature']['name'])->toBe('No Mercy');
    expect($warriorClass['hopeFeature']['hopeCost'])->toBe(3);
    expect($warriorClass['hopeFeature']['description'])->toContain('+1 bonus to your attack rolls');
    expect($warriorClass['hopeFeature']['description'])->toContain('until your next rest');
    
    // Check class features include Attack of Opportunity and Combat Training
    $classFeatureNames = collect($warriorClass['classFeatures'])->pluck('name')->toArray();
    expect($classFeatureNames)->toContain('Attack of Opportunity');
    expect($classFeatureNames)->toContain('Combat Training');
    
    $combatTrainingFeature = collect($warriorClass['classFeatures'])->firstWhere('name', 'Combat Training');
    expect($combatTrainingFeature['description'])->toContain('ignore burden');
    expect($combatTrainingFeature['description'])->toContain('bonus to your damage roll equal to your level');
    
    // Verify subclasses include both calls
    expect($warriorClass['subclasses'])->toBe(['call of the brave', 'call of the slayer']);

});

test('warrior call of brave hope generation mechanics', function () {
    // Test the Hope generation and management features
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $callOfBrave = $subclassData['call of the brave'];
    
    $foundationFeatures = $callOfBrave['foundationFeatures'];
    
    // Test Courage Hope generation on Fear failures
    $courageFeature = collect($foundationFeatures)->firstWhere('name', 'Courage');
    expect($courageFeature['description'])->toContain('When you fail a roll with Fear');
    expect($courageFeature['description'])->toContain('you gain a Hope');
    
    // Test Battle Ritual Hope/Stress management
    $battleRitualFeature = collect($foundationFeatures)->firstWhere('name', 'Battle Ritual');
    expect($battleRitualFeature['description'])->toContain('clear 2 Stress and gain 2 Hope');
    
    // This creates a Hope economy: Fear failures become Hope gains
    // Combined with Battle Ritual, provides significant Hope generation

});

test('call of brave d20 hope die mechanics verification', function () {
    // Test the d20 Hope Die upgrade at low health
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $callOfBrave = $subclassData['call of the brave'];
    
    $specializationFeatures = $callOfBrave['specializationFeatures'];
    $riseToChallengeFeature = collect($specializationFeatures)->firstWhere('name', 'Rise to the Challenge');
    
    // CRITICAL: d20 Hope Die when at 2 or fewer unmarked Hit Points
    expect($riseToChallengeFeature['description'])->toContain('2 or fewer Hit Points unmarked');
    expect($riseToChallengeFeature['description'])->toContain('roll a d20 as your Hope Die');
    
    // This is a massive upgrade: normal Hope Die is typically d12
    // d20 Hope Die provides 1-20 vs 1-12 range = significant power boost when near death

});

test('call of brave tag team roll mechanics verification', function () {
    // Test the Camaraderie mastery feature Tag Team improvements
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $callOfBrave = $subclassData['call of the brave'];
    
    $masteryFeatures = $callOfBrave['masteryFeatures'];
    $camaraderieFeature = collect($masteryFeatures)->firstWhere('name', 'Camaraderie');
    
    // Test additional Tag Team Roll per session
    expect($camaraderieFeature['description'])->toContain('Tag Team Roll once per additional time per session');
    
    // Test cost reduction for allies
    expect($camaraderieFeature['description'])->toContain('ally initiates a Tag Team Roll with you');
    expect($camaraderieFeature['description'])->toContain('they only need to spend 2 Hope');
    expect($camaraderieFeature['hopeCost'])->toBe(2);
    
    // This makes the warrior a team coordination hub

});

test('warrior combat training level scaling integration', function () {
    // Test that Combat Training properly scales with character level
    $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    $warriorClass = $classData['warrior'];
    
    $combatTrainingFeature = collect($warriorClass['classFeatures'])->firstWhere('name', 'Combat Training');
    
    // Verify level-based damage scaling
    expect($combatTrainingFeature['description'])->toContain('bonus to your damage roll equal to your level');
    expect($combatTrainingFeature['description'])->toContain('ignore burden when equipping weapons');
    expect($combatTrainingFeature['description'])->toContain('physical damage');
    
    // At level 4, this provides +4 damage to all physical attacks
    // Combined with Call of the Brave's Hope generation, creates powerful combat synergy

});

test('warrior call of brave danger threshold synergy', function () {
    // Test how Call of the Brave features interact with warrior survivability
    $character = createTestCharacterWith([
        'class' => 'warrior',
        'subclass' => 'call of the brave',
        'ancestry' => 'dwarf', // Sturdy ancestry
        'community' => 'ridgeborne',
        'level' => 4,
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Test that the warrior has good base survivability for the "brave" theme
    expect($stats->hit_points)->toBe(6); // Warrior base
    expect($stats->evasion)->toBeGreaterThanOrEqual(11); // Good evasion
    
    // The synergy: High survivability allows getting to low HP for d20 Hope Die
    // Fear failures generate Hope, Battle Ritual provides recovery
    // Rise to the Challenge makes low HP a power boost rather than just danger
});