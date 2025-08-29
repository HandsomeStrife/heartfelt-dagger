<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Tests\Browser\Helpers\CharacterBuilderHelpers;

/**
 * Comprehensive Pest 4 browser test for Warrior + Call of the Slayer character creation workflow
 * 
 * This test validates the complete character creation process through UI interactions:
 * - Step 1: Class selection (Warrior)
 * - Step 2: Subclass selection (Call of the Slayer)
 * - Step 3: Ancestry selection  
 * - Step 4: Community selection
 * - Step 5: Trait assignment (validates exact -1,0,0,+1,+1,+2 distribution)
 * - Step 6: Character stats verification
 * - Step 7: Equipment selection
 * - Step 8: Background questions
 * - Step 9: Experiences
 * - Step 10: Domain cards (Blade + Bone access)
 * - Step 11: Connections
 * 
 * Also validates:
 * - Warrior base stats (Hit Points: 6, Evasion: 11, Stress: 6)
 * - Call of the Slayer Slayer Dice mechanics display
 * - Domain card limits and access
 * - Character sheet final display with correct modifiers
 */

test('warrior call of slayer complete character creation workflow', function () {
    // Start at character builder main page
    $page = visit('/character-builder');
    
    // Verify initial page load
    $page->assertSee('Character Builder')
         ->waitFor('[dusk="class-card-warrior"]', 10);
    
    // Step 1: Select Warrior class
    $page->click('[dusk="class-card-warrior"]')
         ->waitForText('Warrior', 5)
         ->assertSee('Blade')
         ->assertSee('Bone')
         ->assertSee('Starting Evasion: 11')
         ->assertSee('Starting Hit Points: 6')
         ->assertSee('Warriors are mighty combatants');
    
    // Navigate to subclass selection
    $page->click('[dusk="next-step-button"]')
         ->waitFor('[dusk="subclass-card-call of the slayer"]', 5);
    
    // Step 2: Select Call of the Slayer subclass
    $page->click('[dusk="subclass-card-call of the slayer"]')
         ->waitForText('Call of the Slayer', 5)
         ->assertSee('Slayer Dice')
         ->assertSee('pool of dice')
         ->assertSee('Hope → Slayer Die conversion')
         ->assertSee('Weapon Specialist')
         ->assertSee('Martial Preparation');
    
    // Navigate to ancestry selection
    $page->click('[dusk="next-step-button"]')
         ->waitFor('[dusk="ancestry-card-human"]', 5);
    
    // Step 3: Select Human ancestry (for +2 stress bonus)
    $page->click('[dusk="ancestry-card-human"]')
         ->waitForText('Human', 5)
         ->assertSee('Versatile and adaptable')
         ->assertSee('+2 Stress');
    
    // Navigate to community selection
    $page->click('[dusk="next-step-button"]')
         ->waitFor('[dusk="community-card-wanderborne"]', 5);
    
    // Step 4: Select Wanderborne community
    $page->click('[dusk="community-card-wanderborne"]')
         ->waitForText('Wanderborne', 5)
         ->assertSee('nomadic')
         ->assertSee('traveling');
    
    // Navigate to trait assignment
    $page->click('[dusk="next-step-button"]')
         ->waitFor('[dusk="trait-assignment-section"]', 5);
    
    // Step 5: Assign traits with exact DaggerHeart distribution (-1,0,0,+1,+1,+2)
    $page->click('[dusk="trait-value-agility-1"]')  // +1
         ->wait(0.5)
         ->click('[dusk="trait-value-strength-2"]')  // +2 (primary combat trait)
         ->wait(0.5)
         ->click('[dusk="trait-value-finesse-1"]')   // +1
         ->wait(0.5)
         ->click('[dusk="trait-value-instinct-0"]')  // 0
         ->wait(0.5)
         ->click('[dusk="trait-value-presence-0"]')  // 0
         ->wait(0.5)
         ->click('[dusk="trait-value-knowledge--1"]') // -1
         ->waitForText('Trait assignment complete!', 3);
    
    // Navigate to character info section
    $page->click('[dusk="next-step-button"]')
         ->waitFor('[dusk="character-name-input"]', 5);
    
    // Step 6: Verify character stats are calculated correctly
    $page->assertSee('Hit Points: 8') // 6 base + 2 from human ancestry
         ->assertSee('Evasion: 12')   // 11 base + 1 from agility trait
         ->assertSee('Stress: 8');    // 6 base + 2 from human ancestry
    
    // Fill in character name
    $page->type('[dusk="character-name-input"]', 'Thorin Slayer')
         ->type('[dusk="character-pronouns-input"]', 'he/him')
         ->waitForText('Character details saved', 3);
    
    // Navigate to equipment selection
    $page->click('[dusk="next-step-button"]')
         ->waitFor('[dusk="weapon-selection-section"]', 5);
    
    // Step 7: Select appropriate equipment for warrior
    $page->click('[dusk="weapon-card-greatsword"]')  // Two-handed weapon for high damage
         ->wait(0.5)
         ->click('[dusk="armor-card-chain mail"]')    // Good protection
         ->wait(0.5)
         ->click('[dusk="item-card-warriors kit"]')   // Class-appropriate items
         ->waitForText('Equipment selection complete!', 3);
    
    // Navigate to background questions
    $page->click('[dusk="next-step-button"]')
         ->waitFor('[dusk="background-questions-section"]', 5);
    
    // Step 8: Answer warrior-specific background questions
    $page->type('[dusk="background-answer-0"]', 'I was mentored by a legendary warrior who taught me the way of the blade through countless battles.')
         ->wait(0.5)
         ->type('[dusk="background-answer-1"]', 'I seek vengeance against the orc raiders who destroyed my village and killed my family.')
         ->wait(0.5)
         ->type('[dusk="background-answer-2"]', 'My greatest fear is failing to protect those who depend on me in their darkest hour.')
         ->waitForText('Background complete!', 3);
    
    // Navigate to experiences
    $page->click('[dusk="next-step-button"]')
         ->waitFor('[dusk="experiences-section"]', 5);
    
    // Step 9: Create two experiences
    $page->type('[dusk="experience-0-input"]', 'Battlefield Tactics')
         ->wait(0.5)
         ->type('[dusk="experience-1-input"]', 'Weapon Maintenance')
         ->waitForText('Experiences complete!', 3);
    
    // Navigate to domain cards
    $page->click('[dusk="next-step-button"]')
         ->waitFor('[dusk="domain-cards-section"]', 5);
    
    // Step 10: Select domain cards from Blade and Bone domains
    $page->assertSee('Available Domains: Blade, Bone')
         ->click('[dusk="domain-card-blade-strike"]')  // Level 1 Blade card
         ->wait(0.5)
         ->click('[dusk="domain-card-bone-break"]')    // Level 1 Bone card
         ->waitForText('Domain cards selected!', 3)
         ->assertSee('2/2 cards selected');
    
    // Navigate to connections
    $page->click('[dusk="next-step-button"]')
         ->waitFor('[dusk="connections-section"]', 5);
    
    // Step 11: Answer connection questions
    $page->type('[dusk="connection-answer-0"]', 'We trained together under the same master and forged an unbreakable bond.')
         ->wait(0.5)
         ->type('[dusk="connection-answer-1"]', 'You witnessed my greatest failure and helped me overcome the shame.')
         ->wait(0.5)
         ->type('[dusk="connection-answer-2"]', 'We share a secret mission that could change the fate of our homeland.')
         ->waitForText('Connections complete!', 3);
    
    // Navigate to final character sheet
    $page->click('[dusk="finish-character-button"]')
         ->waitFor('[dusk="character-sheet-final"]', 5);
    
    // Final validation: Verify complete character sheet
    $page->assertSee('Thorin Slayer')
         ->assertSee('Warrior')
         ->assertSee('Call of the Slayer')
         ->assertSee('Human')
         ->assertSee('Wanderborne')
         ->assertSee('Hit Points: 8')
         ->assertSee('Evasion: 12')
         ->assertSee('Stress: 8')
         ->assertSee('Strength: +2')
         ->assertSee('Agility: +1')
         ->assertSee('Finesse: +1')
         ->assertSee('Instinct: 0')
         ->assertSee('Presence: 0')
         ->assertSee('Knowledge: -1')
         ->assertSee('Slayer Dice')
         ->assertSee('Blade Strike')
         ->assertSee('Bone Break')
         ->assertSee('Greatsword')
         ->assertSee('Chain Mail')
         ->assertSee('Battlefield Tactics')
         ->assertSee('Weapon Maintenance');
    
    // Verify navigation to character viewer works
    $page->click('[dusk="view-character-button"]')
         ->waitFor('[dusk="character-viewer"]', 5)
         ->assertSee('Thorin Slayer')
         ->assertSee('Warrior • Call of the Slayer')
         ->assertSee('Human Wanderborne');
    
    // Extract character key from URL to verify database persistence
    $currentUrl = $page->driver->getCurrentURL();
    preg_match('/character-viewer\/([A-Z0-9]{8,})/', $currentUrl, $matches);
    expect($matches)->toHaveCount(2);
    
    $characterKey = $matches[1];
    
    // Verify character was saved correctly in database
    $character = Character::where('character_key', $characterKey)->first();
    expect($character)->not()->toBeNull()
        ->class->toBe('warrior')
        ->subclass->toBe('call of the slayer')
        ->ancestry->toBe('human')
        ->community->toBe('wanderborne')
        ->name->toBe('Thorin Slayer')
        ->pronouns->toBe('he/him');
    
    // Verify traits were saved with correct distribution
    $traits = $character->traits()->pluck('trait_value', 'trait_name')->toArray();
    expect($traits)->toBe([
        'agility' => 1,
        'strength' => 2,
        'finesse' => 1,
        'instinct' => 0,
        'presence' => 0,
        'knowledge' => -1,
    ]);
    
    // Verify character stats calculation
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(8);  // 6 base + 2 human
    expect($stats->evasion)->toBe(12);     // 11 base + 1 agility
    expect($stats->stress)->toBe(8);       // 6 base + 2 human
    
    // Verify domain cards were saved
    $domainCards = $character->domainCards()->pluck('card_name')->toArray();
    expect($domainCards)->toContain('Blade Strike')
                       ->toContain('Bone Break');
    
    // Verify experiences were saved
    $experiences = $character->experiences()->pluck('experience_name')->toArray();
    expect($experiences)->toContain('Battlefield Tactics')
                       ->toContain('Weapon Maintenance');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('warrior call of slayer subclass bonuses validation', function () {
    // Test that Call of the Slayer doesn't provide permanent stat bonuses
    // (its features are resource-based: Slayer Dice, Weapon Specialist, Martial Preparation)
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitFor('[dusk="class-card-warrior"]', 10)
         ->click('[dusk="class-card-warrior"]')
         ->click('[dusk="next-step-button"]')
         ->waitFor('[dusk="subclass-card-call of the slayer"]', 5)
         ->click('[dusk="subclass-card-call of the slayer"]');
    
    // Verify subclass features are displayed correctly
    $page->assertSee('Slayer Dice')
         ->assertSee('Hope → Slayer Die conversion')
         ->assertSee('store a number equal to your Proficiency')
         ->assertSee('end of each session')
         ->assertSee('gain a Hope per die cleared')
         ->assertSee('Weapon Specialist')
         ->assertSee('secondary weapon')
         ->assertSee('reroll any 1s')
         ->assertSee('once per long rest')
         ->assertSee('Martial Preparation')
         ->assertSee('party gains access')
         ->assertSee('d6 Slayer Die');
    
    // Verify no permanent stat bonuses are shown
    $page->assertDontSee('+1 Evasion')
         ->assertDontSee('+1 Hit Points')
         ->assertDontSee('+1 Stress')
         ->assertDontSee('permanent bonus');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('warrior call of slayer domain access verification', function () {
    // Test that Warrior class provides correct domain access (Blade + Bone)
    // and Call of the Slayer doesn't modify domain access
    
    $page = visit('/character-builder');
    
    // Navigate to domain cards step
    $page->waitFor('[dusk="class-card-warrior"]', 10)
         ->click('[dusk="class-card-warrior"]')
         ->click('[dusk="next-step-button"]')
         ->waitFor('[dusk="subclass-card-call of the slayer"]', 5)
         ->click('[dusk="subclass-card-call of the slayer"]');
    
    // Skip to domain cards section
    for ($i = 0; $i < 7; $i++) { // Skip ancestry, community, traits, info, equipment, background, experiences
        $page->click('[dusk="next-step-button"]')->wait(1);
    }
    
    $page->waitFor('[dusk="domain-cards-section"]', 5);
    
    // Verify correct domains are available
    $page->assertSee('Available Domains: Blade, Bone')
         ->assertSee('Blade Domain')
         ->assertSee('Bone Domain');
    
    // Verify level 1 cards from both domains are available
    $page->assertPresent('[dusk="domain-card-blade-strike"]')
         ->assertPresent('[dusk="domain-card-bone-break"]');
    
    // Verify domain cards from other domains are NOT available
    $page->assertDontSee('Arcana Domain')
         ->assertDontSee('Grace Domain')
         ->assertDontSee('Splendor Domain');
    
    // Verify can select exactly 2 cards
    $page->click('[dusk="domain-card-blade-strike"]')
         ->wait(0.5)
         ->assertSee('1/2 cards selected')
         ->click('[dusk="domain-card-bone-break"]')
         ->wait(0.5)
         ->assertSee('2/2 cards selected');
    
    // Verify cannot select a third card (buttons should be disabled)
    $page->assertAttribute('[dusk="domain-card-blade-cleave"]', 'disabled', 'true');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
