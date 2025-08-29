<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive Pest 4 browser test for Rogue + Nightwalker character creation workflow
 * 
 * This test validates the complete character creation process through UI interactions:
 * - Step 1: Class selection (Rogue)
 * - Step 2: Subclass selection (Nightwalker)
 * - Step 3: Ancestry selection  
 * - Step 4: Community selection
 * - Step 5: Trait assignment (validates exact -1,0,0,+1,+1,+2 distribution)
 * - Step 6: Character stats verification
 * - Step 7: Equipment selection
 * - Step 8: Background questions
 * - Step 9: Experiences
 * - Step 10: Domain cards (Midnight + Grace access)
 * - Step 11: Connections
 * 
 * Rogue + Nightwalker specific validations:
 * - Rogue base stats (Hit Points: 4, Evasion: 10, Stress: 6)
 * - Nightwalker uses Finesse as spellcast trait
 * - Shadow Stepper: shadow-to-shadow movement with Cloaked condition
 * - Dark Cloud: area denial and concealment creation
 * - Adrenaline: damage bonus while Vulnerable
 * - Fleeting Shadow: permanent +1 Evasion and extended Shadow Stepper range
 * - Vanishing Act: on-demand Cloaked condition with stress cost
 * - Marked for Death integration with stealth mechanics
 */

test('rogue nightwalker complete character creation workflow', function () {
    // Start at character builder main page
    $page = visit('/character-builder');
    
    // Verify initial page load
    $page->assertSee('Character Builder')
         ->waitForText('Rogue', 10);
    
    // Step 1: Select Rogue class
    $page->click('Rogue')
         ->waitForText('Rogue', 5)
         ->assertSee('Midnight')
         ->assertSee('Grace')
         ->assertSee('Starting Evasion: 10')
         ->assertSee('Starting Hit Points: 4')
         ->assertSee('Marked for Death')
         ->assertSee('stealth and cunning');
    
    // Navigate to subclass selection
    $page->click('Next')
         ->waitForText('Nightwalker', 5);
    
    // Step 2: Select Nightwalker subclass
    $page->click('Nightwalker')
         ->waitForText('Nightwalker', 5)
         ->assertSee('Shadow Stepper')
         ->assertSee('move from shadow to shadow')
         ->assertSee('mark a Stress')
         ->assertSee('reappear inside another shadow')
         ->assertSee('within Far range')
         ->assertSee('you are Cloaked')
         ->assertSee('Dark Cloud')
         ->assertSee('temporary dark cloud')
         ->assertSee('covers any area within Close range')
         ->assertSee('Adrenaline')
         ->assertSee('While you\'re Vulnerable')
         ->assertSee('add your level to your damage rolls')
         ->assertSee('Fleeting Shadow')
         ->assertSee('+1 bonus to your Evasion')
         ->assertSee('Very Far range')
         ->assertSee('Vanishing Act')
         ->assertSee('become Cloaked at any time')
         ->assertSee('Spellcast Trait: Finesse');
    
    // Navigate to ancestry selection
    $page->click('Next')
         ->waitForText('Choose Your Ancestry', 5);
    
    // Step 3: Select Fairy ancestry (thematic for shadow magic)
    $page->click('Fairy')
         ->waitForText('Fairy', 5)
         ->assertSee('Small magical creatures')
         ->assertSee('flight');
    
    // Navigate to community selection
    $page->click('Next')
         ->waitForText('Choose Your Community', 5);
    
    // Step 4: Select Slyborne community (urban, fits rogue theme)
    $page->click('Slyborne')
         ->waitForText('Slyborne', 5)
         ->assertSee('Urban')
         ->assertSee('street-smart');
    
    // Navigate to trait assignment
    $page->click('Next')
         ->waitForText('Assign Your Traits', 5);
    
    // Step 5: Assign traits with exact DaggerHeart distribution (-1,0,0,+1,+1,+2)
    // Focus on Finesse (spellcast trait) and stealth capabilities
    $page->click('Agility: +1')   // +1 (mobility for stealth)
         ->wait(0.5)
         ->click('Strength: -1')  // -1 (not physical focused)
         ->wait(0.5)
         ->click('Finesse: +2')   // +2 (primary spellcast trait)
         ->wait(0.5)
         ->click('Instinct: +1')  // +1 (awareness for stealth)
         ->wait(0.5)
         ->click('Presence: 0')   // 0
         ->wait(0.5)
         ->click('Knowledge: 0')  // 0
         ->waitForText('Trait assignment complete!', 3);
    
    // Navigate to character info section
    $page->click('Next')
         ->waitForText('Character Information', 5);
    
    // Step 6: Verify character stats are calculated correctly
    // Rogue base: 4 HP, 10 Evasion, 6 Stress
    // Agility +1 affects evasion
    // Nightwalker provides +1 permanent Evasion bonus (Fleeting Shadow)
    $page->assertSee('Hit Points: 4')  // Rogue base
         ->assertSee('Evasion: 12')    // 10 base + 1 agility + 1 Fleeting Shadow
         ->assertSee('Stress: 6')      // Rogue base
         ->assertSee('Spellcast Trait: Finesse (+2)');
    
    // Fill in character name
    $page->type('#character-name', 'Shade Silkstep')
         ->type('#character-pronouns', 'they/them')
         ->waitForText('Character details saved', 3);
    
    // Navigate to equipment selection
    $page->click('Next')
         ->waitForText('Choose Your Equipment', 5);
    
    // Step 7: Select appropriate equipment for rogue
    $page->click('Daggers')          // Classic rogue weapons
         ->wait(0.5)
         ->click('Leather Armor')     // Light armor for stealth
         ->wait(0.5)
         ->click('Thieves\' Tools')   // Rogue-specific equipment
         ->waitForText('Equipment selection complete!', 3);
    
    // Navigate to background questions
    $page->click('Next')
         ->waitForText('Background Questions', 5);
    
    // Step 8: Answer rogue-specific background questions
    $page->type('#background-0', 'I learned to slip through shadows after witnessing my family\'s assassination, using darkness as my only protection.')
         ->wait(0.5)
         ->type('#background-1', 'The crime lord who destroyed my life still walks free, and I\'ve spent years learning his weaknesses through the shadow network.')
         ->wait(0.5)
         ->type('#background-2', 'I fear that the shadows I\'ve embraced are slowly consuming my ability to exist in the light with normal people.')
         ->waitForText('Background complete!', 3);
    
    // Navigate to experiences
    $page->click('Next')
         ->waitForText('Choose Your Experiences', 5);
    
    // Step 9: Create two experiences
    $page->type('#experience-0', 'Shadow Dancing')
         ->wait(0.5)
         ->type('#experience-1', 'Urban Infiltration')
         ->waitForText('Experiences complete!', 3);
    
    // Navigate to domain cards
    $page->click('Next')
         ->waitForText('Choose Domain Cards', 5);
    
    // Step 10: Select domain cards from Midnight and Grace domains
    $page->assertSee('Available Domains: Midnight, Grace')
         ->click('Shadow Bolt')       // Level 1 Midnight card
         ->wait(0.5)
         ->click('Charm Person')      // Level 1 Grace card
         ->waitForText('Domain cards selected!', 3)
         ->assertSee('2/2 cards selected');
    
    // Navigate to connections
    $page->click('Next')
         ->waitForText('Character Connections', 5);
    
    // Step 11: Answer connection questions
    $page->type('#connection-0', 'You found me bleeding in an alley after a job went wrong and nursed me back to health without asking questions.')
         ->wait(0.5)
         ->type('#connection-1', 'I accidentally discovered one of your secrets while following a target, and now we both know too much about each other.')
         ->wait(0.5)
         ->type('#connection-2', 'We share knowledge of a hidden passage through the city that connects the noble district to the underground.')
         ->waitForText('Connections complete!', 3);
    
    // Navigate to final character sheet
    $page->click('Finish Character')
         ->waitForText('Shade Silkstep', 5);
    
    // Final validation: Verify complete character sheet
    $page->assertSee('Shade Silkstep')
         ->assertSee('Rogue')
         ->assertSee('Nightwalker')
         ->assertSee('Fairy')
         ->assertSee('Slyborne')
         ->assertSee('Hit Points: 4')
         ->assertSee('Evasion: 12')
         ->assertSee('Stress: 6')
         ->assertSee('Agility: +1')
         ->assertSee('Strength: -1')
         ->assertSee('Finesse: +2')
         ->assertSee('Instinct: +1')
         ->assertSee('Presence: 0')
         ->assertSee('Knowledge: 0')
         ->assertSee('Shadow Stepper')
         ->assertSee('Dark Cloud')
         ->assertSee('Adrenaline')
         ->assertSee('Fleeting Shadow')
         ->assertSee('Vanishing Act')
         ->assertSee('Shadow Bolt')
         ->assertSee('Charm Person')
         ->assertSee('Daggers')
         ->assertSee('Leather Armor')
         ->assertSee('Shadow Dancing')
         ->assertSee('Urban Infiltration');
    
    // Verify navigation to character viewer works
    $page->click('View Character')
         ->waitForText('Shade Silkstep', 5)
         ->assertSee('Rogue • Nightwalker')
         ->assertSee('Fairy Slyborne');
    
    // Extract character key from URL to verify database persistence
    $currentUrl = $page->driver->getCurrentURL();
    preg_match('/character-viewer\/([A-Z0-9]{8,})/', $currentUrl, $matches);
    expect($matches)->toHaveCount(2);
    
    $characterKey = $matches[1];
    
    // Verify character was saved correctly in database
    $character = Character::where('character_key', $characterKey)->first();
    expect($character)->not()->toBeNull()
        ->class->toBe('rogue')
        ->subclass->toBe('nightwalker')
        ->ancestry->toBe('fairy')
        ->community->toBe('slyborne')
        ->name->toBe('Shade Silkstep')
        ->pronouns->toBe('they/them');
    
    // Verify traits were saved with correct distribution
    $traits = $character->traits()->pluck('trait_value', 'trait_name')->toArray();
    expect($traits)->toBe([
        'agility' => 1,
        'strength' => -1,
        'finesse' => 2,
        'instinct' => 1,
        'presence' => 0,
        'knowledge' => 0,
    ]);
    
    // Verify character stats calculation
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(4);  // Rogue base
    expect($stats->evasion)->toBe(12);    // 10 base + 1 agility + 1 Fleeting Shadow
    expect($stats->stress)->toBe(6);      // Rogue base
    
    // Verify subclass bonuses
    expect($character->getSubclassEvasionBonus())->toBe(1); // Fleeting Shadow permanent bonus
    
    // Verify spellcast trait is correct
    expect($character->getSpellcastTrait())->toBe('finesse');
    expect($character->getSpellcastTraitValue())->toBe(2);
    
    // Verify domain cards were saved
    $domainCards = $character->domainCards()->pluck('card_name')->toArray();
    expect($domainCards)->toContain('Shadow Bolt')
                       ->toContain('Charm Person');
    
    // Verify experiences were saved
    $experiences = $character->experiences()->pluck('experience_name')->toArray();
    expect($experiences)->toContain('Shadow Dancing')
                       ->toContain('Urban Infiltration');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('rogue nightwalker shadow mechanics validation', function () {
    // Test that Nightwalker shadow mechanics are properly displayed
    // and stress/range mechanics are explained
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Rogue', 10)
         ->click('Rogue')
         ->click('Next')
         ->waitForText('Nightwalker', 5)
         ->click('Nightwalker');
    
    // Verify Shadow Stepper mechanics
    $page->assertSee('Shadow Stepper')
         ->assertSee('move from shadow to shadow')
         ->assertSee('area of darkness or a shadow cast by another creature')
         ->assertSee('mark a Stress to disappear')
         ->assertSee('reappear inside another shadow within Far range')
         ->assertSee('you are Cloaked');
    
    // Verify Dark Cloud mechanics
    $page->assertSee('Dark Cloud')
         ->assertSee('Make a Spellcast Roll (15)')
         ->assertSee('create a temporary dark cloud')
         ->assertSee('covers any area within Close range')
         ->assertSee('can\'t see outside of it')
         ->assertSee('can\'t see in')
         ->assertSee('Cloaked from any adversary');
    
    // Verify combat enhancement
    $page->assertSee('Adrenaline')
         ->assertSee('While you\'re Vulnerable')
         ->assertSee('add your level to your damage rolls');
    
    // Verify mastery improvements
    $page->assertSee('Fleeting Shadow')
         ->assertSee('permanent +1 bonus to your Evasion')
         ->assertSee('Very Far range');
    
    $page->assertSee('Vanishing Act')
         ->assertSee('Mark a Stress to become Cloaked at any time')
         ->assertSee('automatically clear the Restrained condition')
         ->assertSee('until you roll with Fear or until your next rest');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('rogue nightwalker marked for death integration', function () {
    // Test that Nightwalker stealth mechanics integrate with Rogue's Marked for Death
    
    $page = visit('/character-builder');
    
    // Navigate through class selection
    $page->waitForText('Rogue', 10)
         ->click('Rogue');
    
    // Verify Rogue's Marked for Death feature is shown
    $page->assertSee('Marked for Death')
         ->assertSee('d4 dice')
         ->assertSee('roll them and add their result to the damage roll');
    
    // Continue to subclass
    $page->click('Next')
         ->waitForText('Nightwalker', 5)
         ->click('Nightwalker');
    
    // Verify Nightwalker stealth features complement Marked for Death
    $page->assertSee('Shadow Stepper')  // Positioning for surprise attacks
         ->assertSee('Cloaked')         // Hidden status for sneak attacks
         ->assertSee('Dark Cloud')      // Area denial for tactical advantage
         ->assertSee('Adrenaline');     // Damage bonus when at risk
    
    // The synergy: Marked for Death provides damage scaling,
    // Nightwalker provides positioning and concealment for optimal strikes
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('rogue nightwalker domain access verification', function () {
    // Test that Rogue class provides correct domain access (Midnight + Grace)
    // and Nightwalker doesn't modify domain access
    
    $page = visit('/character-builder');
    
    // Navigate to domain cards step
    $page->waitForText('Rogue', 10)
         ->click('Rogue')
         ->click('Next')
         ->waitForText('Nightwalker', 5)
         ->click('Nightwalker');
    
    // Skip to domain cards section
    for ($i = 0; $i < 7; $i++) { // Skip ancestry, community, traits, info, equipment, background, experiences
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Choose Domain Cards', 5);
    
    // Verify correct domains are available
    $page->assertSee('Available Domains: Midnight, Grace')
         ->assertSee('Midnight Domain')
         ->assertSee('Grace Domain');
    
    // Verify level 1 cards from both domains are available
    $page->assertPresent('[data-testid="domain-card-shadow-bolt"]')
         ->assertPresent('[data-testid="domain-card-charm-person"]');
    
    // Verify domain cards from other domains are NOT available
    $page->assertDontSee('Arcana Domain')
         ->assertDontSee('Blade Domain')
         ->assertDontSee('Valor Domain')
         ->assertDontSee('Bone Domain');
    
    // Verify can select exactly 2 cards
    $page->click('Shadow Bolt')
         ->wait(0.5)
         ->assertSee('1/2 cards selected')
         ->click('Charm Person')
         ->wait(0.5)
         ->assertSee('2/2 cards selected');
    
    // Verify cannot select a third card (buttons should be disabled)
    $remainingCards = $page->elements('[data-testid*="domain-card-"]:not([data-selected])');
    foreach ($remainingCards as $card) {
        expect($card->getAttribute('disabled'))->toBe('true');
    }
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('rogue nightwalker evasion bonus validation', function () {
    // Test that Nightwalker's Fleeting Shadow provides permanent +1 Evasion
    // This is one of the few subclasses that provides a permanent stat bonus
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Rogue', 10)
         ->click('Rogue')
         ->click('Next')
         ->waitForText('Nightwalker', 5)
         ->click('Nightwalker');
    
    // Verify Fleeting Shadow permanent bonus is clearly stated
    $page->assertSee('Fleeting Shadow')
         ->assertSee('permanent +1 bonus to your Evasion');
    
    // Navigate to stats to verify this is applied
    for ($i = 0; $i < 5; $i++) { // Skip to character info
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Character Information', 5);
    
    // Should see base evasion + trait bonuses + Fleeting Shadow bonus
    // Exact value depends on trait assignment, but bonus should be reflected
    $page->assertSee('Nightwalker Evasion Bonus: +1');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('rogue nightwalker spellcast trait validation', function () {
    // Test that Nightwalker correctly assigns Finesse as spellcast trait
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Rogue', 10)
         ->click('Rogue')
         ->click('Next')
         ->waitForText('Nightwalker', 5)
         ->click('Nightwalker');
    
    // Verify spellcast trait is displayed
    $page->assertSee('Spellcast Trait: Finesse')
         ->assertSee('Your shadow magic uses Finesse');
    
    // Navigate to trait assignment to verify integration
    for ($i = 0; $i < 3; $i++) { // Skip ancestry, community
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Assign Your Traits', 5);
    
    // Verify Finesse is highlighted as spellcast trait
    $page->assertSee('Finesse (Spellcast)')
         ->assertSee('This is your primary shadow magic trait');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
