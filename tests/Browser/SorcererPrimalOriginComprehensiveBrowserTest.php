<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive Pest 4 browser test for Sorcerer + Primal Origin character creation workflow
 * 
 * This test validates the complete character creation process through UI interactions:
 * - Step 1: Class selection (Sorcerer)
 * - Step 2: Subclass selection (Primal Origin)
 * - Step 3: Ancestry selection  
 * - Step 4: Community selection
 * - Step 5: Trait assignment (validates exact -1,0,0,+1,+1,+2 distribution)
 * - Step 6: Character stats verification
 * - Step 7: Equipment selection
 * - Step 8: Background questions
 * - Step 9: Experiences
 * - Step 10: Domain cards (Arcana + Midnight access)
 * - Step 11: Connections
 * 
 * Sorcerer + Primal Origin specific validations:
 * - Sorcerer base stats (Hit Points: 6, Evasion: 10, Stress: 6)
 * - Primal Origin uses Instinct as spellcast trait
 * - Wild Magic Surge: unpredictable magical effects
 * - Chaos Manipulation: reality-bending chaotic magic
 * - Primal Instincts: enhanced survival and perception
 * - Raw Power: unrefined but potent magical abilities
 * - Chaos Storm: area-effect wild magic
 * - Primordial Force: ultimate chaotic magic control
 * - Natural Evasion and Transcendence class feature integration
 */

test('sorcerer primal origin complete character creation workflow', function () {
    // Start at character builder main page
    $page = visit('/character-builder');
    
    // Verify initial page load
    $page->assertSee('Character Builder')
         ->waitForText('Sorcerer', 10);
    
    // Step 1: Select Sorcerer class
    $page->click('Sorcerer')
         ->waitForText('Sorcerer', 5)
         ->assertSee('Arcana')
         ->assertSee('Midnight')
         ->assertSee('Starting Evasion: 10')
         ->assertSee('Starting Hit Points: 6')
         ->assertSee('Natural Evasion')
         ->assertSee('Transcendence')
         ->assertSee('raw magical power');
    
    // Navigate to subclass selection
    $page->click('Next')
         ->waitForText('Primal Origin', 5);
    
    // Step 2: Select Primal Origin subclass
    $page->click('Primal Origin')
         ->waitForText('Primal Origin', 5)
         ->assertSee('Wild Magic Surge')
         ->assertSee('unpredictable magical effects')
         ->assertSee('chaotic energy')
         ->assertSee('random magical phenomena')
         ->assertSee('Chaos Manipulation')
         ->assertSee('reality-bending chaotic magic')
         ->assertSee('alter probability')
         ->assertSee('bend the laws of reality')
         ->assertSee('Primal Instincts')
         ->assertSee('enhanced survival')
         ->assertSee('perception beyond normal limits')
         ->assertSee('primitive awareness')
         ->assertSee('Raw Power')
         ->assertSee('unrefined but potent')
         ->assertSee('explosive magical energy')
         ->assertSee('overwhelming force')
         ->assertSee('Chaos Storm')
         ->assertSee('area-effect wild magic')
         ->assertSee('storm of chaotic energy')
         ->assertSee('Primordial Force')
         ->assertSee('ultimate chaotic magic control')
         ->assertSee('harness primal forces')
         ->assertSee('Spellcast Trait: Instinct');
    
    // Navigate to ancestry selection
    $page->click('Next')
         ->waitForText('Choose Your Ancestry', 5);
    
    // Step 3: Select Drakona ancestry (wild magical heritage)
    $page->click('Drakona')
         ->waitForText('Drakona', 5)
         ->assertSee('Dragon-blooded')
         ->assertSee('elemental breath');
    
    // Navigate to community selection
    $page->click('Next')
         ->waitForText('Choose Your Community', 5);
    
    // Step 4: Select Wildborne community (primal, untamed origins)
    $page->click('Wildborne')
         ->waitForText('Wildborne', 5)
         ->assertSee('Wilderness-dwelling')
         ->assertSee('survival-focused');
    
    // Navigate to trait assignment
    $page->click('Next')
         ->waitForText('Assign Your Traits', 5);
    
    // Step 5: Assign traits with exact DaggerHeart distribution (-1,0,0,+1,+1,+2)
    // Focus on Instinct (spellcast trait) and primal capabilities
    $page->click('Agility: +1')   // +1 (wild movement)
         ->wait(0.5)
         ->click('Strength: +1')  // +1 (primal strength)
         ->wait(0.5)
         ->click('Finesse: 0')    // 0
         ->wait(0.5)
         ->click('Instinct: +2')  // +2 (primary spellcast trait)
         ->wait(0.5)
         ->click('Presence: 0')   // 0
         ->wait(0.5)
         ->click('Knowledge: -1') // -1 (less academic, more intuitive)
         ->waitForText('Trait assignment complete!', 3);
    
    // Navigate to character info section
    $page->click('Next')
         ->waitForText('Character Information', 5);
    
    // Step 6: Verify character stats are calculated correctly
    // Sorcerer base: 6 HP, 10 Evasion, 6 Stress
    // Agility +1 affects evasion
    $page->assertSee('Hit Points: 6')  // Sorcerer base
         ->assertSee('Evasion: 11')    // 10 base + 1 agility
         ->assertSee('Stress: 6')      // Sorcerer base
         ->assertSee('Spellcast Trait: Instinct (+2)');
    
    // Fill in character name
    $page->type('#character-name', 'Tempest Wildmage')
         ->type('#character-pronouns', 'they/them')
         ->waitForText('Character details saved', 3);
    
    // Navigate to equipment selection
    $page->click('Next')
         ->waitForText('Choose Your Equipment', 5);
    
    // Step 7: Select appropriate equipment for primal sorcerer
    $page->click('Chaos Staff')       // Unpredictable magical focus
         ->wait(0.5)
         ->click('Wild Robes')        // Primal attire
         ->wait(0.5)
         ->click('Primal Tokens')     // Chaotic components
         ->waitForText('Equipment selection complete!', 3);
    
    // Navigate to background questions
    $page->click('Next')
         ->waitForText('Background Questions', 5);
    
    // Step 8: Answer sorcerer-specific background questions
    $page->type('#background-0', 'I was born during a magical storm, and ever since then my magic has been wild and unpredictable.')
         ->wait(0.5)
         ->type('#background-1', 'Ancient chaotic energies are awakening across the land, and I can feel them calling to the magic in my blood.')
         ->wait(0.5)
         ->type('#background-2', 'I fear that my wild magic will one day spiral completely out of control and harm those I care about.')
         ->waitForText('Background complete!', 3);
    
    // Navigate to experiences
    $page->click('Next')
         ->waitForText('Choose Your Experiences', 5);
    
    // Step 9: Create two experiences
    $page->type('#experience-0', 'Chaos Channeling')
         ->wait(0.5)
         ->type('#experience-1', 'Primal Survival')
         ->waitForText('Experiences complete!', 3);
    
    // Navigate to domain cards
    $page->click('Next')
         ->waitForText('Choose Domain Cards', 5);
    
    // Step 10: Select domain cards from Arcana and Midnight domains
    $page->assertSee('Available Domains: Arcana, Midnight')
         ->click('Wild Surge')        // Level 1 Arcana card
         ->wait(0.5)
         ->click('Chaos Bolt')        // Level 1 Midnight card
         ->waitForText('Domain cards selected!', 3)
         ->assertSee('2/2 cards selected');
    
    // Navigate to connections
    $page->click('Next')
         ->waitForText('Character Connections', 5);
    
    // Step 11: Answer connection questions
    $page->type('#connection-0', 'My wild magic accidentally transported you across the realm, and now we\'re bound by that chaotic journey.')
         ->wait(0.5)
         ->type('#connection-1', 'You were the first person to not fear my unpredictable magic, accepting me when others called me dangerous.')
         ->wait(0.5)
         ->type('#connection-2', 'We both carry traces of ancient magical forces that react to each other in strange and powerful ways.')
         ->waitForText('Connections complete!', 3);
    
    // Navigate to final character sheet
    $page->click('Finish Character')
         ->waitForText('Tempest Wildmage', 5);
    
    // Final validation: Verify complete character sheet
    $page->assertSee('Tempest Wildmage')
         ->assertSee('Sorcerer')
         ->assertSee('Primal Origin')
         ->assertSee('Drakona')
         ->assertSee('Wildborne')
         ->assertSee('Hit Points: 6')
         ->assertSee('Evasion: 11')
         ->assertSee('Stress: 6')
         ->assertSee('Agility: +1')
         ->assertSee('Strength: +1')
         ->assertSee('Finesse: 0')
         ->assertSee('Instinct: +2')
         ->assertSee('Presence: 0')
         ->assertSee('Knowledge: -1')
         ->assertSee('Natural Evasion')
         ->assertSee('Transcendence')
         ->assertSee('Wild Magic Surge')
         ->assertSee('Chaos Manipulation')
         ->assertSee('Primal Instincts')
         ->assertSee('Raw Power')
         ->assertSee('Chaos Storm')
         ->assertSee('Primordial Force')
         ->assertSee('Wild Surge')
         ->assertSee('Chaos Bolt')
         ->assertSee('Chaos Staff')
         ->assertSee('Wild Robes')
         ->assertSee('Chaos Channeling')
         ->assertSee('Primal Survival');
    
    // Verify navigation to character viewer works
    $page->click('View Character')
         ->waitForText('Tempest Wildmage', 5)
         ->assertSee('Sorcerer • Primal Origin')
         ->assertSee('Drakona Wildborne');
    
    // Extract character key from URL to verify database persistence
    $currentUrl = $page->driver->getCurrentURL();
    preg_match('/character-viewer\/([A-Z0-9]{8,})/', $currentUrl, $matches);
    expect($matches)->toHaveCount(2);
    
    $characterKey = $matches[1];
    
    // Verify character was saved correctly in database
    $character = Character::where('character_key', $characterKey)->first();
    expect($character)->not()->toBeNull()
        ->class->toBe('sorcerer')
        ->subclass->toBe('primal origin')
        ->ancestry->toBe('drakona')
        ->community->toBe('wildborne')
        ->name->toBe('Tempest Wildmage')
        ->pronouns->toBe('they/them');
    
    // Verify traits were saved with correct distribution
    $traits = $character->traits()->pluck('trait_value', 'trait_name')->toArray();
    expect($traits)->toBe([
        'agility' => 1,
        'strength' => 1,
        'finesse' => 0,
        'instinct' => 2,
        'presence' => 0,
        'knowledge' => -1,
    ]);
    
    // Verify character stats calculation
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(6);  // Sorcerer base
    expect($stats->evasion)->toBe(11);    // 10 base + 1 agility
    expect($stats->stress)->toBe(6);      // Sorcerer base
    
    // Verify spellcast trait is correct
    expect($character->getSpellcastTrait())->toBe('instinct');
    expect($character->getSpellcastTraitValue())->toBe(2);
    
    // Verify domain cards were saved
    $domainCards = $character->domainCards()->pluck('card_name')->toArray();
    expect($domainCards)->toContain('Wild Surge')
                       ->toContain('Chaos Bolt');
    
    // Verify experiences were saved
    $experiences = $character->experiences()->pluck('experience_name')->toArray();
    expect($experiences)->toContain('Chaos Channeling')
                       ->toContain('Primal Survival');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('sorcerer primal origin wild magic mechanics validation', function () {
    // Test that Primal Origin's wild magic and chaos mechanics are properly displayed
    // and unpredictable effects are explained
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Sorcerer', 10)
         ->click('Sorcerer')
         ->click('Next')
         ->waitForText('Primal Origin', 5)
         ->click('Primal Origin');
    
    // Verify wild magic mechanics
    $page->assertSee('Wild Magic Surge')
         ->assertSee('unpredictable magical effects')
         ->assertSee('chaotic energy')
         ->assertSee('random magical phenomena')
         ->assertSee('uncontrolled bursts');
    
    // Verify chaos manipulation
    $page->assertSee('Chaos Manipulation')
         ->assertSee('reality-bending chaotic magic')
         ->assertSee('alter probability')
         ->assertSee('bend the laws of reality')
         ->assertSee('chaotic intervention');
    
    // Verify area chaos effects
    $page->assertSee('Chaos Storm')
         ->assertSee('area-effect wild magic')
         ->assertSee('storm of chaotic energy')
         ->assertSee('unpredictable effects')
         ->assertSee('environmental chaos');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('sorcerer primal origin primal instincts validation', function () {
    // Test that Primal Origin's instinctual and survival abilities are displayed
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Sorcerer', 10)
         ->click('Sorcerer')
         ->click('Next')
         ->waitForText('Primal Origin', 5)
         ->click('Primal Origin');
    
    // Verify primal features
    $page->assertSee('Primal Instincts')
         ->assertSee('enhanced survival')
         ->assertSee('perception beyond normal limits')
         ->assertSee('primitive awareness')
         ->assertSee('ancient knowledge');
    
    // Verify raw power
    $page->assertSee('Raw Power')
         ->assertSee('unrefined but potent')
         ->assertSee('explosive magical energy')
         ->assertSee('overwhelming force')
         ->assertSee('brute magical strength');
    
    $page->assertSee('Primordial Force')
         ->assertSee('ultimate chaotic magic control')
         ->assertSee('harness primal forces')
         ->assertSee('master of chaos')
         ->assertSee('ancient magical power');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('sorcerer primal origin natural evasion integration validation', function () {
    // Test that Primal Origin features integrate with Sorcerer's Natural Evasion
    
    $page = visit('/character-builder');
    
    // Navigate through class selection
    $page->waitForText('Sorcerer', 10)
         ->click('Sorcerer');
    
    // Verify Sorcerer's Natural Evasion features are shown
    $page->assertSee('Natural Evasion')
         ->assertSee('instinctive dodge')
         ->assertSee('magical protection')
         ->assertSee('Transcendence')
         ->assertSee('channel raw magical energy')
         ->assertSee('bypass physical limitations');
    
    // Continue to subclass
    $page->click('Next')
         ->waitForText('Primal Origin', 5)
         ->click('Primal Origin');
    
    // Verify Primal Origin enhances instinctual abilities
    $page->assertSee('Primal Instincts')    // Enhanced survival instincts
         ->assertSee('Wild Magic Surge')    // Unpredictable defensive reactions
         ->assertSee('Chaos Manipulation'); // Reality-bending evasion
    
    // The synergy: Natural Evasion for instinctive defense,
    // Primal Origin adds chaotic unpredictability and enhanced instincts
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('sorcerer primal origin transcendence integration validation', function () {
    // Test that Primal Origin features enhance Sorcerer's Transcendence
    
    $page = visit('/character-builder');
    
    // Navigate through class selection
    $page->waitForText('Sorcerer', 10)
         ->click('Sorcerer');
    
    // Verify Sorcerer's Transcendence features
    $page->assertSee('Transcendence')
         ->assertSee('channel raw magical energy')
         ->assertSee('bypass physical limitations')
         ->assertSee('magical transformation');
    
    // Continue to subclass
    $page->click('Next')
         ->waitForText('Primal Origin', 5)
         ->click('Primal Origin');
    
    // Verify Primal Origin adds chaotic transcendence
    $page->assertSee('Raw Power')          // Unrefined but potent energy
         ->assertSee('Primordial Force')   // Ultimate magical control
         ->assertSee('Chaos Storm');       // Area transcendence effects
    
    // The synergy: Transcendence provides magical transformation,
    // Primal Origin adds chaotic, primal, and unpredictable elements
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('sorcerer primal origin domain access verification', function () {
    // Test that Sorcerer class provides correct domain access (Arcana + Midnight)
    // and Primal Origin doesn't modify domain access
    
    $page = visit('/character-builder');
    
    // Navigate to domain cards step
    $page->waitForText('Sorcerer', 10)
         ->click('Sorcerer')
         ->click('Next')
         ->waitForText('Primal Origin', 5)
         ->click('Primal Origin');
    
    // Skip to domain cards section
    for ($i = 0; $i < 7; $i++) { // Skip ancestry, community, traits, info, equipment, background, experiences
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Choose Domain Cards', 5);
    
    // Verify correct domains are available
    $page->assertSee('Available Domains: Arcana, Midnight')
         ->assertSee('Arcana Domain')
         ->assertSee('Midnight Domain');
    
    // Verify level 1 cards from both domains are available
    $page->assertPresent('[data-testid="domain-card-wild-surge"]')
         ->assertPresent('[data-testid="domain-card-chaos-bolt"]');
    
    // Verify domain cards from other domains are NOT available
    $page->assertDontSee('Splendor Domain')
         ->assertDontSee('Valor Domain')
         ->assertDontSee('Grace Domain')
         ->assertDontSee('Sage Domain');
    
    // Verify can select exactly 2 cards
    $page->click('Wild Surge')
         ->wait(0.5)
         ->assertSee('1/2 cards selected')
         ->click('Chaos Bolt')
         ->wait(0.5)
         ->assertSee('2/2 cards selected');
    
    // Verify cannot select a third card (buttons should be disabled)
    $remainingCards = $page->elements('[data-testid*="domain-card-"]:not([data-selected])');
    foreach ($remainingCards as $card) {
        expect($card->getAttribute('disabled'))->toBe('true');
    }
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('sorcerer primal origin instinct scaling validation', function () {
    // Test that Primal Origin's Instinct-based abilities are properly explained
    // Primal awareness and survival scaling with Instinct
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Sorcerer', 10)
         ->click('Sorcerer')
         ->click('Next')
         ->waitForText('Primal Origin', 5)
         ->click('Primal Origin');
    
    // Verify Instinct scaling is clearly explained
    $page->assertSee('Primal Instincts')
         ->assertSee('survival scales with Instinct')
         ->assertSee('primitive awareness enhanced by intuition');
    
    $page->assertSee('Wild Magic Surge')
         ->assertSee('chaos effects influenced by Instinct')
         ->assertSee('instinctual magic control');
    
    // Verify spellcast trait connection
    $page->assertSee('Spellcast Trait: Instinct')
         ->assertSee('Your chaotic magic uses Instinct');
    
    // Navigate to trait assignment to verify integration
    for ($i = 0; $i < 3; $i++) { // Skip ancestry, community
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Assign Your Traits', 5);
    
    // Verify Instinct is highlighted as spellcast trait
    $page->assertSee('Instinct (Spellcast)')
         ->assertSee('This trait affects your primal magic and chaos control');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('sorcerer primal origin spellcast trait validation', function () {
    // Test that Primal Origin correctly assigns Instinct as spellcast trait
    // (different from Elemental Origin which might use different trait)
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Sorcerer', 10)
         ->click('Sorcerer')
         ->click('Next')
         ->waitForText('Primal Origin', 5)
         ->click('Primal Origin');
    
    // Verify spellcast trait is displayed
    $page->assertSee('Spellcast Trait: Instinct')
         ->assertSee('Your primal magic uses Instinct')
         ->assertSee('chaotic and instinctual');
    
    // Navigate to trait assignment to verify integration
    for ($i = 0; $i < 3; $i++) { // Skip ancestry, community
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Assign Your Traits', 5);
    
    // Verify Instinct is highlighted as spellcast trait
    $page->assertSee('Instinct (Spellcast)')
         ->assertSee('This is your primary primal chaos trait');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
