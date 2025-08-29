<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive Pest 4 browser test for Druid + Warden of the Elements character creation workflow
 * 
 * This test validates the complete character creation process through UI interactions:
 * - Step 1: Class selection (Druid)
 * - Step 2: Subclass selection (Warden of the Elements)
 * - Step 3: Ancestry selection  
 * - Step 4: Community selection
 * - Step 5: Trait assignment (validates exact -1,0,0,+1,+1,+2 distribution)
 * - Step 6: Character stats verification
 * - Step 7: Equipment selection
 * - Step 8: Background questions
 * - Step 9: Experiences
 * - Step 10: Domain cards (Sage + Arcana access)
 * - Step 11: Connections
 * 
 * Druid + Warden of the Elements specific validations:
 * - Druid base stats (Hit Points: 5, Evasion: 9, Stress: 6)
 * - Warden of the Elements uses Instinct as spellcast trait
 * - Elemental Form: enhanced Beastform with elemental creatures
 * - Primal Command: elemental creature summoning and control
 * - Elemental Mastery: enhanced elemental magic effects
 * - Storm Caller: weather and natural force manipulation
 * - Nature's Avatar: massive elemental transformation
 * - World Shaper: terrain and environment alteration
 * - Evolution Hope feature integration with elemental focus
 */

test('druid warden of elements complete character creation workflow', function () {
    // Start at character builder main page
    $page = visit('/character-builder');
    
    // Verify initial page load
    $page->assertSee('Character Builder')
         ->waitForText('Druid', 10);
    
    // Step 1: Select Druid class
    $page->click('Druid')
         ->waitForText('Druid', 5)
         ->assertSee('Sage')
         ->assertSee('Arcana')
         ->assertSee('Starting Evasion: 9')
         ->assertSee('Starting Hit Points: 5')
         ->assertSee('Beastform')
         ->assertSee('Wildtouch')
         ->assertSee('Evolution')
         ->assertSee('nature magic and transformation');
    
    // Navigate to subclass selection
    $page->click('Next')
         ->waitForText('Warden of the Elements', 5);
    
    // Step 2: Select Warden of the Elements subclass
    $page->click('Warden of the Elements')
         ->waitForText('Warden of the Elements', 5)
         ->assertSee('Elemental Form')
         ->assertSee('transform into elemental creatures')
         ->assertSee('fire elementals')
         ->assertSee('air elementals')
         ->assertSee('earth elementals')
         ->assertSee('water elementals')
         ->assertSee('Primal Command')
         ->assertSee('summon elemental creatures')
         ->assertSee('spend Hope to maintain control')
         ->assertSee('Elemental Mastery')
         ->assertSee('elemental magic effects')
         ->assertSee('enhanced damage')
         ->assertSee('environmental effects')
         ->assertSee('Storm Caller')
         ->assertSee('manipulate weather')
         ->assertSee('create storms')
         ->assertSee('control natural forces')
         ->assertSee('Nature\'s Avatar')
         ->assertSee('massive elemental form')
         ->assertSee('World Shaper')
         ->assertSee('reshape terrain')
         ->assertSee('alter environment')
         ->assertSee('Spellcast Trait: Instinct');
    
    // Navigate to ancestry selection
    $page->click('Next')
         ->waitForText('Choose Your Ancestry', 5);
    
    // Step 3: Select Drakona ancestry (elemental connection with breath weapon)
    $page->click('Drakona')
         ->waitForText('Drakona', 5)
         ->assertSee('Dragon-blooded')
         ->assertSee('elemental breath');
    
    // Navigate to community selection
    $page->click('Next')
         ->waitForText('Choose Your Community', 5);
    
    // Step 4: Select Wildborne community (perfect for elemental druid)
    $page->click('Wildborne')
         ->waitForText('Wildborne', 5)
         ->assertSee('Wilderness-dwelling')
         ->assertSee('survival-focused');
    
    // Navigate to trait assignment
    $page->click('Next')
         ->waitForText('Assign Your Traits', 5);
    
    // Step 5: Assign traits with exact DaggerHeart distribution (-1,0,0,+1,+1,+2)
    // Focus on Instinct (spellcast trait) and elemental capabilities
    $page->click('Agility: +1')   // +1 (mobility with elements)
         ->wait(0.5)
         ->click('Strength: +1')  // +1 (elemental power)
         ->wait(0.5)
         ->click('Finesse: 0')    // 0
         ->wait(0.5)
         ->click('Instinct: +2')  // +2 (primary spellcast trait)
         ->wait(0.5)
         ->click('Presence: 0')   // 0
         ->wait(0.5)
         ->click('Knowledge: -1') // -1 (less academic focus)
         ->waitForText('Trait assignment complete!', 3);
    
    // Navigate to character info section
    $page->click('Next')
         ->waitForText('Character Information', 5);
    
    // Step 6: Verify character stats are calculated correctly
    // Druid base: 5 HP, 9 Evasion, 6 Stress
    // Agility +1 affects evasion
    $page->assertSee('Hit Points: 5')  // Druid base
         ->assertSee('Evasion: 10')    // 9 base + 1 agility
         ->assertSee('Stress: 6')      // Druid base
         ->assertSee('Spellcast Trait: Instinct (+2)');
    
    // Fill in character name
    $page->type('#character-name', 'Tempest Stormcaller')
         ->type('#character-pronouns', 'he/him')
         ->waitForText('Character details saved', 3);
    
    // Navigate to equipment selection
    $page->click('Next')
         ->waitForText('Choose Your Equipment', 5);
    
    // Step 7: Select appropriate equipment for elemental druid
    $page->click('Elemental Staff')   // Magical focus
         ->wait(0.5)
         ->click('Leather Armor')     // Light armor for mobility
         ->wait(0.5)
         ->click('Elemental Stones')  // Elemental components
         ->waitForText('Equipment selection complete!', 3);
    
    // Navigate to background questions
    $page->click('Next')
         ->waitForText('Background Questions', 5);
    
    // Step 8: Answer druid-specific background questions
    $page->type('#background-0', 'I was struck by lightning during a great storm and awakened to find I could command the very elements themselves.')
         ->wait(0.5)
         ->type('#background-1', 'An ancient elemental lord has been imprisoned, and the imbalance is causing devastating natural disasters across the land.')
         ->wait(0.5)
         ->type('#background-2', 'I fear that my elemental power is growing beyond my control and might one day consume everything I care about.')
         ->waitForText('Background complete!', 3);
    
    // Navigate to experiences
    $page->click('Next')
         ->waitForText('Choose Your Experiences', 5);
    
    // Step 9: Create two experiences
    $page->type('#experience-0', 'Elemental Binding')
         ->wait(0.5)
         ->type('#experience-1', 'Weather Prediction')
         ->waitForText('Experiences complete!', 3);
    
    // Navigate to domain cards
    $page->click('Next')
         ->waitForText('Choose Domain Cards', 5);
    
    // Step 10: Select domain cards from Sage and Arcana domains
    $page->assertSee('Available Domains: Sage, Arcana')
         ->click('Entangle')          // Level 1 Sage card
         ->wait(0.5)
         ->click('Lightning Bolt')    // Level 1 Arcana card
         ->waitForText('Domain cards selected!', 3)
         ->assertSee('2/2 cards selected');
    
    // Navigate to connections
    $page->click('Next')
         ->waitForText('Character Connections', 5);
    
    // Step 11: Answer connection questions
    $page->type('#connection-0', 'I saved you from a wildfire by summoning a rainstorm, but the effort nearly killed me from exhaustion.')
         ->wait(0.5)
         ->type('#connection-1', 'You helped me track down an elemental that had gone rogue and was terrorizing a village.')
         ->wait(0.5)
         ->type('#connection-2', 'We both witnessed the birth of a new elemental spirit, and now we share a mystical connection to that realm.')
         ->waitForText('Connections complete!', 3);
    
    // Navigate to final character sheet
    $page->click('Finish Character')
         ->waitForText('Tempest Stormcaller', 5);
    
    // Final validation: Verify complete character sheet
    $page->assertSee('Tempest Stormcaller')
         ->assertSee('Druid')
         ->assertSee('Warden of the Elements')
         ->assertSee('Drakona')
         ->assertSee('Wildborne')
         ->assertSee('Hit Points: 5')
         ->assertSee('Evasion: 10')
         ->assertSee('Stress: 6')
         ->assertSee('Agility: +1')
         ->assertSee('Strength: +1')
         ->assertSee('Finesse: 0')
         ->assertSee('Instinct: +2')
         ->assertSee('Presence: 0')
         ->assertSee('Knowledge: -1')
         ->assertSee('Beastform')
         ->assertSee('Wildtouch')
         ->assertSee('Elemental Form')
         ->assertSee('Primal Command')
         ->assertSee('Elemental Mastery')
         ->assertSee('Storm Caller')
         ->assertSee('Nature\'s Avatar')
         ->assertSee('World Shaper')
         ->assertSee('Entangle')
         ->assertSee('Lightning Bolt')
         ->assertSee('Elemental Staff')
         ->assertSee('Leather Armor')
         ->assertSee('Elemental Binding')
         ->assertSee('Weather Prediction');
    
    // Verify navigation to character viewer works
    $page->click('View Character')
         ->waitForText('Tempest Stormcaller', 5)
         ->assertSee('Druid • Warden of the Elements')
         ->assertSee('Drakona Wildborne');
    
    // Extract character key from URL to verify database persistence
    $currentUrl = $page->driver->getCurrentURL();
    preg_match('/character-viewer\/([A-Z0-9]{8,})/', $currentUrl, $matches);
    expect($matches)->toHaveCount(2);
    
    $characterKey = $matches[1];
    
    // Verify character was saved correctly in database
    $character = Character::where('character_key', $characterKey)->first();
    expect($character)->not()->toBeNull()
        ->class->toBe('druid')
        ->subclass->toBe('warden of the elements')
        ->ancestry->toBe('drakona')
        ->community->toBe('wildborne')
        ->name->toBe('Tempest Stormcaller')
        ->pronouns->toBe('he/him');
    
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
    expect($stats->hit_points)->toBe(5);  // Druid base
    expect($stats->evasion)->toBe(10);    // 9 base + 1 agility
    expect($stats->stress)->toBe(6);      // Druid base
    
    // Verify spellcast trait is correct
    expect($character->getSpellcastTrait())->toBe('instinct');
    expect($character->getSpellcastTraitValue())->toBe(2);
    
    // Verify domain cards were saved
    $domainCards = $character->domainCards()->pluck('card_name')->toArray();
    expect($domainCards)->toContain('Entangle')
                       ->toContain('Lightning Bolt');
    
    // Verify experiences were saved
    $experiences = $character->experiences()->pluck('experience_name')->toArray();
    expect($experiences)->toContain('Elemental Binding')
                       ->toContain('Weather Prediction');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('druid warden of elements elemental form mechanics validation', function () {
    // Test that Warden of the Elements' Elemental Form enhancement to Beastform is displayed
    // and elemental creature options are explained
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Druid', 10)
         ->click('Druid')
         ->click('Next')
         ->waitForText('Warden of the Elements', 5)
         ->click('Warden of the Elements');
    
    // Verify Elemental Form mechanics
    $page->assertSee('Elemental Form')
         ->assertSee('transform into elemental creatures')
         ->assertSee('fire elementals')
         ->assertSee('air elementals')
         ->assertSee('earth elementals')
         ->assertSee('water elementals')
         ->assertSee('gain elemental immunities')
         ->assertSee('enhanced magical damage');
    
    // Verify progression to massive forms
    $page->assertSee('Nature\'s Avatar')
         ->assertSee('massive elemental form')
         ->assertSee('size of a building')
         ->assertSee('devastating elemental attacks');
    
    // This enhances the base Druid Beastform with elemental options
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('druid warden of elements summoning mechanics validation', function () {
    // Test that Warden of the Elements' summoning and control abilities are displayed
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Druid', 10)
         ->click('Druid')
         ->click('Next')
         ->waitForText('Warden of the Elements', 5)
         ->click('Warden of the Elements');
    
    // Verify summoning features
    $page->assertSee('Primal Command')
         ->assertSee('summon elemental creatures')
         ->assertSee('spend Hope to maintain control')
         ->assertSee('elemental follows your commands')
         ->assertSee('until your next rest');
    
    // Verify environmental control
    $page->assertSee('Storm Caller')
         ->assertSee('manipulate weather')
         ->assertSee('create storms')
         ->assertSee('control natural forces')
         ->assertSee('lightning')
         ->assertSee('wind')
         ->assertSee('rain');
    
    $page->assertSee('World Shaper')
         ->assertSee('reshape terrain')
         ->assertSee('alter environment')
         ->assertSee('create barriers')
         ->assertSee('change landscape');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('druid warden of elements evolution integration validation', function () {
    // Test that Warden of the Elements features integrate with Druid's Evolution
    
    $page = visit('/character-builder');
    
    // Navigate through class selection
    $page->waitForText('Druid', 10)
         ->click('Druid');
    
    // Verify Druid's Evolution features are shown
    $page->assertSee('Evolution')
         ->assertSee('Spend 3 Hope to transform into a Beastform')
         ->assertSee('without marking a Stress')
         ->assertSee('choose one trait to raise by +1');
    
    // Continue to subclass
    $page->click('Next')
         ->waitForText('Warden of the Elements', 5)
         ->click('Warden of the Elements');
    
    // Verify Warden of the Elements enhances Evolution with elemental focus
    $page->assertSee('Elemental Form')     // Enhanced transformation options
         ->assertSee('elemental creatures') // Access to elemental rather than just animal forms
         ->assertSee('Primal Command')      // Hope-based elemental summoning
         ->assertSee('Nature\'s Avatar');   // Ultimate elemental transformation
    
    // The synergy: Evolution provides enhanced transformation,
    // Warden of the Elements provides elemental creature access and environmental control
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('druid warden of elements hope economy validation', function () {
    // Test that Warden of the Elements' Hope costs are properly balanced
    // Evolution: 3 Hope, Primal Command: Hope for summoning control
    
    $page = visit('/character-builder');
    
    // Navigate through class selection
    $page->waitForText('Druid', 10)
         ->click('Druid');
    
    // Verify Druid's Evolution Hope cost
    $page->assertSee('Evolution')
         ->assertSee('Spend 3 Hope to transform into a Beastform')
         ->assertSee('without marking a Stress');
    
    // Continue to subclass
    $page->click('Next')
         ->waitForText('Warden of the Elements', 5)
         ->click('Warden of the Elements');
    
    // Verify Hope-based summoning costs
    $page->assertSee('Primal Command')
         ->assertSee('spend Hope to maintain control')
         ->assertSee('elemental follows your commands');
    
    // Hope economy analysis:
    // - Evolution: 3 Hope for enhanced transformation
    // - Primal Command: Variable Hope for elemental summoning control
    // This encourages tactical Hope management between personal power and summoning allies
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('druid warden of elements domain access verification', function () {
    // Test that Druid class provides correct domain access (Sage + Arcana)
    // and Warden of the Elements doesn't modify domain access
    
    $page = visit('/character-builder');
    
    // Navigate to domain cards step
    $page->waitForText('Druid', 10)
         ->click('Druid')
         ->click('Next')
         ->waitForText('Warden of the Elements', 5)
         ->click('Warden of the Elements');
    
    // Skip to domain cards section
    for ($i = 0; $i < 7; $i++) { // Skip ancestry, community, traits, info, equipment, background, experiences
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Choose Domain Cards', 5);
    
    // Verify correct domains are available
    $page->assertSee('Available Domains: Sage, Arcana')
         ->assertSee('Sage Domain')
         ->assertSee('Arcana Domain');
    
    // Verify level 1 cards from both domains are available
    $page->assertPresent('[data-testid="domain-card-entangle"]')
         ->assertPresent('[data-testid="domain-card-lightning-bolt"]');
    
    // Verify domain cards from other domains are NOT available
    $page->assertDontSee('Valor Domain')
         ->assertDontSee('Blade Domain')
         ->assertDontSee('Grace Domain')
         ->assertDontSee('Midnight Domain');
    
    // Verify can select exactly 2 cards
    $page->click('Entangle')
         ->wait(0.5)
         ->assertSee('1/2 cards selected')
         ->click('Lightning Bolt')
         ->wait(0.5)
         ->assertSee('2/2 cards selected');
    
    // Verify cannot select a third card (buttons should be disabled)
    $remainingCards = $page->elements('[data-testid*="domain-card-"]:not([data-selected])');
    foreach ($remainingCards as $card) {
        expect($card->getAttribute('disabled'))->toBe('true');
    }
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('druid warden of elements instinct scaling validation', function () {
    // Test that Warden of the Elements' Instinct-based abilities are properly explained
    // Elemental magic power scaling with Instinct
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Druid', 10)
         ->click('Druid')
         ->click('Next')
         ->waitForText('Warden of the Elements', 5)
         ->click('Warden of the Elements');
    
    // Verify Instinct scaling is clearly explained
    $page->assertSee('Elemental Mastery')
         ->assertSee('elemental magic effects')
         ->assertSee('enhanced damage based on Instinct')
         ->assertSee('environmental effects scale with power');
    
    // Verify spellcast trait connection
    $page->assertSee('Spellcast Trait: Instinct')
         ->assertSee('Your elemental magic uses Instinct');
    
    // Navigate to trait assignment to verify integration
    for ($i = 0; $i < 3; $i++) { // Skip ancestry, community
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Assign Your Traits', 5);
    
    // Verify Instinct is highlighted as spellcast trait
    $page->assertSee('Instinct (Spellcast)')
         ->assertSee('This trait affects your elemental power');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
