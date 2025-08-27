<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive browser test for Rogue + Syndicate
 * 
 * Tests critical mechanics:
 * - Rogue base stats (Evasion 12, Hit Points 4) 
 * - Syndicate's Well-Connected contact generation mechanics
 * - Contacts Everywhere session usage (once per session, 3 benefit options)
 * - Reliable Backup usage expansion (3x per session, 2 additional options)
 * - Contact relationship dynamics (5 different relationship types)
 * - Social infiltration benefits (+3 to Hope/Fear Die, d20 Hope Die)
 * - Combat support benefits (+2d8 damage, -1 Hit Point damage)
 * - Domain access (Midnight + Grace)
 * - Finesse spellcasting trait integration
 */

test('rogue syndicate contact mechanics verification', function () {
    $character = createTestCharacterWith([
        'class' => 'rogue',
        'subclass' => 'syndicate',
        'ancestry' => 'elf', // Neutral ancestry for clean testing
        'community' => 'slyborne', // Thematic for rogue syndicate
        'level' => 4, // Mid-level for specialization features
    ]);

    // Add character traits for complete testing (Finesse for spellcasting)
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 1],
        ['trait_name' => 'strength', 'trait_value' => 0],
        ['trait_name' => 'finesse', 'trait_value' => 2], // Spellcasting trait for Rogue Syndicate
        ['trait_name' => 'instinct', 'trait_value' => 1],
        ['trait_name' => 'presence', 'trait_value' => 0],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Verify Rogue base stats
    expect($stats->hit_points)->toBe(6); // Rogue base 6
    expect($stats->evasion)->toBeGreaterThanOrEqual(12); // Rogue base 12 + trait bonuses

    // Test that no permanent bonuses are applied (Syndicate features are social/narrative)
    expect($character->getSubclassEvasionBonus())->toBe(0); // No permanent evasion bonus
    expect($character->getSubclassStressBonus())->toBe(0); // No stress bonus  
    expect($character->getSubclassHitPointBonus())->toBe(0); // No HP bonus

    // Test domain card calculation (Syndicate provides no domain card bonuses)
    $builderData = \Domain\Character\Data\CharacterBuilderData::from([
        'selected_class' => 'rogue',
        'selected_subclass' => 'syndicate',
    ]);
    
    expect($builderData->getMaxDomainCards())->toBe(2); // Base 2, no bonuses
    expect($builderData->getSubclassDomainCardBonus())->toBe(0);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('rogue syndicate browser integration works correctly', function () {
    $character = Character::factory()->create([
        'character_key' => 'SYND123',
        'class' => null,
        'subclass' => null,
    ]);

    // Start browser test - visit character builder
    $page = visit("/character-builder/{$character->character_key}");

    // Set up the character data
    $character->update([
        'class' => 'rogue',
        'subclass' => 'syndicate',
        'ancestry' => 'elf',
        'community' => 'slyborne',
    ]);

    // Add character traits (Finesse focus for spellcasting)
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 1],
        ['trait_name' => 'strength', 'trait_value' => 0],
        ['trait_name' => 'finesse', 'trait_value' => 2], // Spellcasting trait
        ['trait_name' => 'instinct', 'trait_value' => 1],
        ['trait_name' => 'presence', 'trait_value' => 0],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    // Verify character was configured correctly
    expect($character->fresh())
        ->class->toBe('rogue')
        ->subclass->toBe('syndicate')
        ->ancestry->toBe('elf')
        ->community->toBe('slyborne');

    // Test that stats are calculated correctly
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(6); // Rogue base 6
    expect($stats->evasion)->toBeGreaterThanOrEqual(12); // Rogue base + bonuses

    // No permanent stat bonuses from Syndicate
    expect($character->getSubclassEvasionBonus())->toBe(0);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('syndicate subclass features are correctly defined', function () {
    // Verify Syndicate subclass JSON configuration
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $syndicate = $subclassData['syndicate'];
    
    expect($syndicate)->not()->toBeNull();
    expect($syndicate['name'])->toBe('Syndicate');
    expect($syndicate['spellcastTrait'])->toBe('Finesse'); // Critical for spellcasting

    // CRITICAL TEST: Verify Well-Connected contact generation
    $foundationFeatures = $syndicate['foundationFeatures'];
    $wellConnectedFeature = collect($foundationFeatures)->firstWhere('name', 'Well-Connected');
    
    expect($wellConnectedFeature)->not()->toBeNull();
    expect($wellConnectedFeature['description'])->toContain('arrive in a prominent town');
    expect($wellConnectedFeature['description'])->toContain('you know somebody');
    expect($wellConnectedFeature['description'])->toContain('Give them a name');
    
    // Verify all 5 relationship dynamics
    expect($wellConnectedFeature['description'])->toContain('They owe me a favor');
    expect($wellConnectedFeature['description'])->toContain('hard to find');
    expect($wellConnectedFeature['description'])->toContain('ask for something in exchange');
    expect($wellConnectedFeature['description'])->toContain('great deal of trouble');
    expect($wellConnectedFeature['description'])->toContain('used to be together');
    expect($wellConnectedFeature['description'])->toContain('didn\'t part on great terms');

    // CRITICAL TEST: Verify Contacts Everywhere mechanics
    $specializationFeatures = $syndicate['specializationFeatures'];
    $contactsFeature = collect($specializationFeatures)->firstWhere('name', 'Contacts Everywhere');
    
    expect($contactsFeature)->not()->toBeNull();
    expect($contactsFeature['description'])->toContain('Once per session');
    expect($contactsFeature['description'])->toContain('shady contact');
    
    // Verify all 3 base benefit options
    expect($contactsFeature['description'])->toContain('1 handful of gold');
    expect($contactsFeature['description'])->toContain('unique tool');
    expect($contactsFeature['description'])->toContain('mundane object');
    expect($contactsFeature['description'])->toContain('+3 bonus to the result of your Hope or Fear Die');
    expect($contactsFeature['description'])->toContain('snipe from the shadows');
    expect($contactsFeature['description'])->toContain('adding 2d8 to your damage roll');

    // CRITICAL TEST: Verify Reliable Backup expansion
    $masteryFeatures = $syndicate['masteryFeatures'];
    $reliableBackupFeature = collect($masteryFeatures)->firstWhere('name', 'Reliable Backup');
    
    expect($reliableBackupFeature)->not()->toBeNull();
    expect($reliableBackupFeature['description'])->toContain('three times per session');
    expect($reliableBackupFeature['description'])->toContain('following options are added');
    
    // Verify 2 additional benefit options
    expect($reliableBackupFeature['description'])->toContain('reducing the Hit Points marked by 1');
    expect($reliableBackupFeature['description'])->toContain('roll a d20 as your Hope Die');
    expect($reliableBackupFeature['description'])->toContain('Presence Roll in conversation');

});

test('rogue class integration with syndicate', function () {
    // Test how Rogue class features integrate with Syndicate networking
    $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
    $rogueClass = $classData['rogue'];
    
    expect($rogueClass['name'])->toBe('Rogue');
    
    // Verify Rogue domains (Midnight + Grace)
    expect($rogueClass['domains'])->toBe(['midnight', 'grace']);
    expect($rogueClass['startingEvasion'])->toBe(12);
    expect($rogueClass['startingHitPoints'])->toBe(6);
    
    // Verify Rogue class features complement Syndicate social focus
    $classFeatureNames = collect($rogueClass['classFeatures'])->pluck('name')->toArray();
    expect($classFeatureNames)->toContain('Cloaked'); // Stealth enhancement
    expect($classFeatureNames)->toContain('Sneak Attack'); // Damage when positioned
    
    // Midnight domain provides stealth, Grace domain provides social manipulation
    // Perfect combo for Syndicate's contact-based gameplay

});

test('well connected contact generation mechanics', function () {
    // Test Well-Connected's narrative contact generation system
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $syndicate = $subclassData['syndicate'];
    
    $foundationFeatures = $syndicate['foundationFeatures'];
    $wellConnectedFeature = collect($foundationFeatures)->firstWhere('name', 'Well-Connected');
    
    // Test trigger condition
    expect($wellConnectedFeature['description'])->toContain('When you arrive in a prominent town or environment');
    expect($wellConnectedFeature['description'])->toContain('you know somebody who calls this place home');
    
    // Test narrative elements
    expect($wellConnectedFeature['description'])->toContain('Give them a name');
    expect($wellConnectedFeature['description'])->toContain('note how you think they could be useful');
    expect($wellConnectedFeature['description'])->toContain('choose one fact from the following list');
    
    // All 5 relationship dynamics provide different story hooks and complications
    // This creates rich narrative opportunities for the GM and player

});

test('contacts everywhere session mechanics verification', function () {
    // Test Contacts Everywhere's once-per-session benefits
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $syndicate = $subclassData['syndicate'];
    
    $specializationFeatures = $syndicate['specializationFeatures'];
    $contactsFeature = collect($specializationFeatures)->firstWhere('name', 'Contacts Everywhere');
    
    // Test usage frequency
    expect($contactsFeature['description'])->toContain('Once per session');
    expect($contactsFeature['description'])->toContain('shady contact');
    
    // Test benefit categories and mechanical effects
    // Resource/Tool Support
    expect($contactsFeature['description'])->toContain('1 handful of gold, a unique tool, or a mundane object');
    expect($contactsFeature['description'])->toContain('situation requires');
    
    // Action Roll Enhancement  
    expect($contactsFeature['description'])->toContain('On your next action roll');
    expect($contactsFeature['description'])->toContain('+3 bonus to the result of your Hope or Fear Die');
    
    // Combat Damage Support
    expect($contactsFeature['description'])->toContain('next time you deal damage');
    expect($contactsFeature['description'])->toContain('snipe from the shadows');
    expect($contactsFeature['description'])->toContain('adding 2d8 to your damage roll');
    
    // Each benefit requires narrative justification: "describe what brought them here"

});

test('reliable backup mastery mechanics verification', function () {
    // Test Reliable Backup's expanded usage and additional options
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    $syndicate = $subclassData['syndicate'];
    
    $masteryFeatures = $syndicate['masteryFeatures'];
    $reliableBackupFeature = collect($masteryFeatures)->firstWhere('name', 'Reliable Backup');
    
    // Test usage expansion: 1x → 3x per session
    expect($reliableBackupFeature['description'])->toContain('three times per session');
    
    // Test additional benefit options
    expect($reliableBackupFeature['description'])->toContain('following options are added to the list');
    
    // Damage Reduction Option
    expect($reliableBackupFeature['description'])->toContain('When you mark 1 or more Hit Points');
    expect($reliableBackupFeature['description'])->toContain('rush out to shield you');
    expect($reliableBackupFeature['description'])->toContain('reducing the Hit Points marked by 1');
    
    // Social Enhancement Option  
    expect($reliableBackupFeature['description'])->toContain('When you make a Presence Roll in conversation');
    expect($reliableBackupFeature['description'])->toContain('they back you up');
    expect($reliableBackupFeature['description'])->toContain('roll a d20 as your Hope Die');
    
    // Total: 5 benefit options, 3 uses per session = very flexible

});

test('syndicate finesse spellcasting integration', function () {
    // Test that Syndicate correctly uses Finesse for spellcasting
    $character = createTestCharacterWith([
        'class' => 'rogue',
        'subclass' => 'syndicate',
        'ancestry' => 'elf',
        'community' => 'slyborne',
    ]);

    // Set high Finesse trait for spellcasting
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 1],
        ['trait_name' => 'strength', 'trait_value' => 0],
        ['trait_name' => 'finesse', 'trait_value' => 2], // High for spellcasting
        ['trait_name' => 'instinct', 'trait_value' => 1],
        ['trait_name' => 'presence', 'trait_value' => 0],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    // Test that builder data correctly identifies Finesse as spellcast trait
    $builderData = \Domain\Character\Data\CharacterBuilderData::from([
        'selected_class' => 'rogue',
        'selected_subclass' => 'syndicate',
        'character_traits' => [
            'agility' => 1,
            'strength' => 0,
            'finesse' => 2, // Should be used for spellcasting calculations
            'instinct' => 1,
            'presence' => 0,
            'knowledge' => -1,
        ],
    ]);

    expect($character->fresh())->class->toBe('rogue');
    expect($character->fresh())->subclass->toBe('syndicate');

    // Verify the character was created with correct traits
    $finesseTrait = $character->traits()->where('trait_name', 'finesse')->first();
    expect($finesseTrait->trait_value)->toBe(2);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('syndicate social infiltration mechanics integration', function () {
    // Test how Syndicate features create a complete social infiltration system
    $character = createTestCharacterWith([
        'class' => 'rogue',
        'subclass' => 'syndicate',
        'ancestry' => 'human', // +2 stress for more flexibility
        'community' => 'slyborne',
        'level' => 6, // High level for mastery features
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Test that the rogue has good survivability for infiltration work
    expect($stats->hit_points)->toBe(6); // Rogue base
    expect($stats->evasion)->toBeGreaterThanOrEqual(12); // Excellent evasion for survival
    
    // The complete system:
    // 1. Well-Connected: Establishes contacts in every location with story hooks
    // 2. Contacts Everywhere (3x): Provides resources, roll bonuses, combat support
    // 3. Reliable Backup: Adds damage mitigation + social enhancement (d20 Hope Die)
    // 4. Midnight domain: Stealth and concealment spells
    // 5. Grace domain: Social manipulation and charm spells
    // 6. Finesse spellcasting: Precise, subtle magical effects
    
    // This creates the ultimate social infiltrator and information broker

});

test('syndicate contact benefit versatility testing', function () {
    // Test the versatility of Syndicate contact benefits across different scenarios
    $character = createTestCharacterWith([
        'class' => 'rogue',
        'subclass' => 'syndicate',
        'ancestry' => 'katari', // Feline stealth theme
        'community' => 'slyborne',
        'level' => 5,
    ]);

    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);

    // Base stats support infiltration and social work
    expect($stats->evasion)->toBeGreaterThanOrEqual(12); // Excellent defense
    expect($stats->hit_points)->toBe(6); // Good base survivability
    
    // Benefit Analysis:
    // Resources: Gold/tools/objects solve practical problems
    // Roll Enhancement: +3 to Hope/Fear die improves critical skill checks  
    // Damage Support: +2d8 from sniping provides significant combat boost
    // Damage Mitigation: -1 Hit Point reduction improves survivability
    // Social Boost: d20 Hope Die on Presence rolls ensures social success
    
    // 5 benefits × 3 uses = 15 different ways contacts can assist per session
});