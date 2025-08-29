<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive Pest 4 browser test for Seraph + Winged Sentinel character creation workflow
 * 
 * This test validates the complete character creation process through UI interactions:
 * - Step 1: Class selection (Seraph)
 * - Step 2: Subclass selection (Winged Sentinel)
 * - Step 3: Ancestry selection  
 * - Step 4: Community selection
 * - Step 5: Trait assignment (validates exact -1,0,0,+1,+1,+2 distribution)
 * - Step 6: Character stats verification
 * - Step 7: Equipment selection
 * - Step 8: Background questions
 * - Step 9: Experiences
 * - Step 10: Domain cards (Splendor + Valor access)
 * - Step 11: Connections
 * 
 * Seraph + Winged Sentinel specific validations:
 * - Seraph base stats (Hit Points: 7, Evasion: 9, Stress: 6)
 * - Winged Sentinel uses Instinct as spellcast trait
 * - Aerial Mobility: flight and aerial combat capabilities
 * - Divine Sight: enhanced perception and threat detection
 * - Protective Wings: shield allies from damage
 * - Celestial Guardian: area protection and divine intervention
 * - Radiant Strike: ranged divine attacks from above
 * - Heaven's Watch: ultimate surveillance and protection
 * - Prayer Dice and Life Support Hope feature integration
 */

test('seraph winged sentinel complete character creation workflow', function () {
    // Start at character builder main page
    $page = visit('/character-builder');
    
    // Verify initial page load
    $page->assertSee('Character Builder')
         ->waitForText('Seraph', 10);
    
    // Step 1: Select Seraph class
    $page->click('Seraph')
         ->waitForText('Seraph', 5)
         ->assertSee('Splendor')
         ->assertSee('Valor')
         ->assertSee('Starting Evasion: 9')
         ->assertSee('Starting Hit Points: 7')
         ->assertSee('Prayer Dice')
         ->assertSee('Life Support')
         ->assertSee('divine power and protection');
    
    // Navigate to subclass selection
    $page->click('Next')
         ->waitForText('Winged Sentinel', 5);
    
    // Step 2: Select Winged Sentinel subclass
    $page->click('Winged Sentinel')
         ->waitForText('Winged Sentinel', 5)
         ->assertSee('Aerial Mobility')
         ->assertSee('manifest divine wings')
         ->assertSee('fly and maneuver in combat')
         ->assertSee('aerial advantage')
         ->assertSee('Divine Sight')
         ->assertSee('enhanced perception')
         ->assertSee('see through illusions')
         ->assertSee('detect hidden threats')
         ->assertSee('Protective Wings')
         ->assertSee('shield allies from damage')
         ->assertSee('intercept attacks from above')
         ->assertSee('Celestial Guardian')
         ->assertSee('area protection')
         ->assertSee('divine intervention')
         ->assertSee('watch over battlefield')
         ->assertSee('Radiant Strike')
         ->assertSee('ranged divine attacks')
         ->assertSee('strike from the heavens')
         ->assertSee('Heaven\'s Watch')
         ->assertSee('ultimate surveillance')
         ->assertSee('omniscient protection')
         ->assertSee('Spellcast Trait: Instinct');
    
    // Navigate to ancestry selection
    $page->click('Next')
         ->waitForText('Choose Your Ancestry', 5);
    
    // Step 3: Select Fairy ancestry (already has flight, enhances aerial theme)
    $page->click('Fairy')
         ->waitForText('Fairy', 5)
         ->assertSee('Small magical creatures')
         ->assertSee('flight');
    
    // Navigate to community selection
    $page->click('Next')
         ->waitForText('Choose Your Community', 5);
    
    // Step 4: Select Orderborne community (structured, disciplined sentinel)
    $page->click('Orderborne')
         ->waitForText('Orderborne', 5)
         ->assertSee('Disciplined')
         ->assertSee('faith-based')
         ->assertSee('structured');
    
    // Navigate to trait assignment
    $page->click('Next')
         ->waitForText('Assign Your Traits', 5);
    
    // Step 5: Assign traits with exact DaggerHeart distribution (-1,0,0,+1,+1,+2)
    // Focus on Instinct (spellcast trait) and aerial surveillance capabilities
    $page->click('Agility: +1')   // +1 (aerial maneuverability)
         ->wait(0.5)
         ->click('Strength: 0')   // 0
         ->wait(0.5)
         ->click('Finesse: +1')   // +1 (precise ranged attacks)
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
    // Seraph base: 7 HP, 9 Evasion, 6 Stress
    // Agility +1 affects evasion
    $page->assertSee('Hit Points: 7')  // Seraph base
         ->assertSee('Evasion: 10')    // 9 base + 1 agility
         ->assertSee('Stress: 6')      // Seraph base
         ->assertSee('Spellcast Trait: Instinct (+2)');
    
    // Fill in character name
    $page->type('#character-name', 'Aurora Skywarden')
         ->type('#character-pronouns', 'she/her')
         ->waitForText('Character details saved', 3);
    
    // Navigate to equipment selection
    $page->click('Next')
         ->waitForText('Choose Your Equipment', 5);
    
    // Step 7: Select appropriate equipment for aerial sentinel
    $page->click('Longbow')           // Ranged weapon for aerial combat
         ->wait(0.5)
         ->click('Light Armor')       // Mobility for flight
         ->wait(0.5)
         ->click('Holy Symbol')       // Divine focus
         ->waitForText('Equipment selection complete!', 3);
    
    // Navigate to background questions
    $page->click('Next')
         ->waitForText('Background Questions', 5);
    
    // Step 8: Answer seraph-specific background questions
    $page->type('#background-0', 'I was chosen to be the guardian of a sacred mountain peak, watching over the valley below from the clouds above.')
         ->wait(0.5)
         ->type('#background-1', 'Dark creatures are gathering in the shadows, and only my aerial vantage point can track their movements across the land.')
         ->wait(0.5)
         ->type('#background-2', 'I fear that my duty to watch from above keeps me too distant from the people I\'m meant to protect.')
         ->waitForText('Background complete!', 3);
    
    // Navigate to experiences
    $page->click('Next')
         ->waitForText('Choose Your Experiences', 5);
    
    // Step 9: Create two experiences
    $page->type('#experience-0', 'Aerial Navigation')
         ->wait(0.5)
         ->type('#experience-1', 'Threat Assessment')
         ->waitForText('Experiences complete!', 3);
    
    // Navigate to domain cards
    $page->click('Next')
         ->waitForText('Choose Domain Cards', 5);
    
    // Step 10: Select domain cards from Splendor and Valor domains
    $page->assertSee('Available Domains: Splendor, Valor')
         ->click('Guiding Light')     // Level 1 Splendor card
         ->wait(0.5)
         ->click('Inspire')           // Level 1 Valor card
         ->waitForText('Domain cards selected!', 3)
         ->assertSee('2/2 cards selected');
    
    // Navigate to connections
    $page->click('Next')
         ->waitForText('Character Connections', 5);
    
    // Step 11: Answer connection questions
    $page->type('#connection-0', 'I watched over you from the sky during a dangerous journey, and now you feel safe knowing I\'m always watching.')
         ->wait(0.5)
         ->type('#connection-1', 'You climbed a mountain to reach me when I was wounded and couldn\'t fly, risking your life to help me.')
         ->wait(0.5)
         ->type('#connection-2', 'We share a vision of protecting the innocent, though your methods are earthbound while mine are from above.')
         ->waitForText('Connections complete!', 3);
    
    // Navigate to final character sheet
    $page->click('Finish Character')
         ->waitForText('Aurora Skywarden', 5);
    
    // Final validation: Verify complete character sheet
    $page->assertSee('Aurora Skywarden')
         ->assertSee('Seraph')
         ->assertSee('Winged Sentinel')
         ->assertSee('Fairy')
         ->assertSee('Orderborne')
         ->assertSee('Hit Points: 7')
         ->assertSee('Evasion: 10')
         ->assertSee('Stress: 6')
         ->assertSee('Agility: +1')
         ->assertSee('Strength: 0')
         ->assertSee('Finesse: +1')
         ->assertSee('Instinct: +2')
         ->assertSee('Presence: 0')
         ->assertSee('Knowledge: -1')
         ->assertSee('Prayer Dice')
         ->assertSee('Life Support')
         ->assertSee('Aerial Mobility')
         ->assertSee('Divine Sight')
         ->assertSee('Protective Wings')
         ->assertSee('Celestial Guardian')
         ->assertSee('Radiant Strike')
         ->assertSee('Heaven\'s Watch')
         ->assertSee('Guiding Light')
         ->assertSee('Inspire')
         ->assertSee('Longbow')
         ->assertSee('Light Armor')
         ->assertSee('Aerial Navigation')
         ->assertSee('Threat Assessment');
    
    // Verify navigation to character viewer works
    $page->click('View Character')
         ->waitForText('Aurora Skywarden', 5)
         ->assertSee('Seraph • Winged Sentinel')
         ->assertSee('Fairy Orderborne');
    
    // Extract character key from URL to verify database persistence
    $currentUrl = $page->driver->getCurrentURL();
    preg_match('/character-viewer\/([A-Z0-9]{8,})/', $currentUrl, $matches);
    expect($matches)->toHaveCount(2);
    
    $characterKey = $matches[1];
    
    // Verify character was saved correctly in database
    $character = Character::where('character_key', $characterKey)->first();
    expect($character)->not()->toBeNull()
        ->class->toBe('seraph')
        ->subclass->toBe('winged sentinel')
        ->ancestry->toBe('fairy')
        ->community->toBe('orderborne')
        ->name->toBe('Aurora Skywarden')
        ->pronouns->toBe('she/her');
    
    // Verify traits were saved with correct distribution
    $traits = $character->traits()->pluck('trait_value', 'trait_name')->toArray();
    expect($traits)->toBe([
        'agility' => 1,
        'strength' => 0,
        'finesse' => 1,
        'instinct' => 2,
        'presence' => 0,
        'knowledge' => -1,
    ]);
    
    // Verify character stats calculation
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(7);  // Seraph base
    expect($stats->evasion)->toBe(10);    // 9 base + 1 agility
    expect($stats->stress)->toBe(6);      // Seraph base
    
    // Verify spellcast trait is correct
    expect($character->getSpellcastTrait())->toBe('instinct');
    expect($character->getSpellcastTraitValue())->toBe(2);
    
    // Verify domain cards were saved
    $domainCards = $character->domainCards()->pluck('card_name')->toArray();
    expect($domainCards)->toContain('Guiding Light')
                       ->toContain('Inspire');
    
    // Verify experiences were saved
    $experiences = $character->experiences()->pluck('experience_name')->toArray();
    expect($experiences)->toContain('Aerial Navigation')
                       ->toContain('Threat Assessment');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('seraph winged sentinel aerial mechanics validation', function () {
    // Test that Winged Sentinel's flight and aerial combat mechanics are properly displayed
    // and aerial advantage benefits are explained
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Seraph', 10)
         ->click('Seraph')
         ->click('Next')
         ->waitForText('Winged Sentinel', 5)
         ->click('Winged Sentinel');
    
    // Verify aerial mechanics
    $page->assertSee('Aerial Mobility')
         ->assertSee('manifest divine wings')
         ->assertSee('fly and maneuver in combat')
         ->assertSee('aerial advantage')
         ->assertSee('attack from above')
         ->assertSee('difficult to target');
    
    // Verify ranged combat integration
    $page->assertSee('Radiant Strike')
         ->assertSee('ranged divine attacks')
         ->assertSee('strike from the heavens')
         ->assertSee('divine arrows')
         ->assertSee('magical projectiles');
    
    // Verify ultimate aerial mastery
    $page->assertSee('Heaven\'s Watch')
         ->assertSee('ultimate surveillance')
         ->assertSee('omniscient protection')
         ->assertSee('see all threats')
         ->assertSee('coordinate from above');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('seraph winged sentinel protection mechanics validation', function () {
    // Test that Winged Sentinel's protection and surveillance abilities are displayed
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Seraph', 10)
         ->click('Seraph')
         ->click('Next')
         ->waitForText('Winged Sentinel', 5)
         ->click('Winged Sentinel');
    
    // Verify protection features
    $page->assertSee('Protective Wings')
         ->assertSee('shield allies from damage')
         ->assertSee('intercept attacks from above')
         ->assertSee('cover allies')
         ->assertSee('physical barrier');
    
    // Verify surveillance capabilities
    $page->assertSee('Divine Sight')
         ->assertSee('enhanced perception')
         ->assertSee('see through illusions')
         ->assertSee('detect hidden threats')
         ->assertSee('pierce deceptions');
    
    $page->assertSee('Celestial Guardian')
         ->assertSee('area protection')
         ->assertSee('divine intervention')
         ->assertSee('watch over battlefield')
         ->assertSee('coordinate defenses');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('seraph winged sentinel prayer dice integration validation', function () {
    // Test that Winged Sentinel features integrate with Seraph's Prayer Dice
    
    $page = visit('/character-builder');
    
    // Navigate through class selection
    $page->waitForText('Seraph', 10)
         ->click('Seraph');
    
    // Verify Seraph's Prayer Dice features are shown
    $page->assertSee('Prayer Dice')
         ->assertSee('beginning of each session')
         ->assertSee('roll a number of d4s')
         ->assertSee('equal to your subclass\'s Spellcast trait')
         ->assertSee('aid yourself or an ally within Far range')
         ->assertSee('reduce incoming damage')
         ->assertSee('add to a roll\'s result');
    
    // Continue to subclass
    $page->click('Next')
         ->waitForText('Winged Sentinel', 5)
         ->click('Winged Sentinel');
    
    // Verify Winged Sentinel enhances Prayer Dice with aerial advantages
    $page->assertSee('Aerial Mobility')     // Positioning for Prayer Dice range
         ->assertSee('Divine Sight')        // Enhanced target acquisition
         ->assertSee('Protective Wings');   // Physical protection to complement Prayer Dice
    
    // The synergy: Prayer Dice for divine aid from range,
    // Winged Sentinel provides aerial positioning and enhanced surveillance
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('seraph winged sentinel life support integration validation', function () {
    // Test that Winged Sentinel features integrate with Seraph's Life Support
    
    $page = visit('/character-builder');
    
    // Navigate through class selection
    $page->waitForText('Seraph', 10)
         ->click('Seraph');
    
    // Verify Seraph's Life Support Hope feature is shown
    $page->assertSee('Life Support')
         ->assertSee('Spend 3 Hope')
         ->assertSee('clear a Hit Point on an ally within Close range');
    
    // Continue to subclass
    $page->click('Next')
         ->waitForText('Winged Sentinel', 5)
         ->click('Winged Sentinel');
    
    // Verify Winged Sentinel enhances protection with aerial advantages
    $page->assertSee('Protective Wings')    // Physical protection beyond healing
         ->assertSee('Celestial Guardian')  // Area protection capability
         ->assertSee('Divine Sight');       // Early threat detection
    
    // The synergy: Life Support for emergency healing,
    // Winged Sentinel provides positioning and protection to prevent damage
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('seraph winged sentinel domain access verification', function () {
    // Test that Seraph class provides correct domain access (Splendor + Valor)
    // and Winged Sentinel doesn't modify domain access
    
    $page = visit('/character-builder');
    
    // Navigate to domain cards step
    $page->waitForText('Seraph', 10)
         ->click('Seraph')
         ->click('Next')
         ->waitForText('Winged Sentinel', 5)
         ->click('Winged Sentinel');
    
    // Skip to domain cards section
    for ($i = 0; $i < 7; $i++) { // Skip ancestry, community, traits, info, equipment, background, experiences
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Choose Domain Cards', 5);
    
    // Verify correct domains are available
    $page->assertSee('Available Domains: Splendor, Valor')
         ->assertSee('Splendor Domain')
         ->assertSee('Valor Domain');
    
    // Verify level 1 cards from both domains are available
    $page->assertPresent('[data-testid="domain-card-guiding-light"]')
         ->assertPresent('[data-testid="domain-card-inspire"]');
    
    // Verify domain cards from other domains are NOT available
    $page->assertDontSee('Arcana Domain')
         ->assertDontSee('Midnight Domain')
         ->assertDontSee('Grace Domain')
         ->assertDontSee('Bone Domain');
    
    // Verify can select exactly 2 cards
    $page->click('Guiding Light')
         ->wait(0.5)
         ->assertSee('1/2 cards selected')
         ->click('Inspire')
         ->wait(0.5)
         ->assertSee('2/2 cards selected');
    
    // Verify cannot select a third card (buttons should be disabled)
    $remainingCards = $page->elements('[data-testid*="domain-card-"]:not([data-selected])');
    foreach ($remainingCards as $card) {
        expect($card->getAttribute('disabled'))->toBe('true');
    }
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('seraph winged sentinel instinct scaling validation', function () {
    // Test that Winged Sentinel's Instinct-based abilities are properly explained
    // Surveillance and threat detection scaling with Instinct
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Seraph', 10)
         ->click('Seraph')
         ->click('Next')
         ->waitForText('Winged Sentinel', 5)
         ->click('Winged Sentinel');
    
    // Verify Instinct scaling is clearly explained
    $page->assertSee('Divine Sight')
         ->assertSee('perception scales with Instinct')
         ->assertSee('enhanced by divine intuition');
    
    $page->assertSee('Heaven\'s Watch')
         ->assertSee('surveillance effectiveness based on Instinct')
         ->assertSee('omniscient awareness');
    
    // Verify spellcast trait connection
    $page->assertSee('Spellcast Trait: Instinct')
         ->assertSee('Your divine magic uses Instinct');
    
    // Navigate to trait assignment to verify integration
    for ($i = 0; $i < 3; $i++) { // Skip ancestry, community
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Assign Your Traits', 5);
    
    // Verify Instinct is highlighted as spellcast trait
    $page->assertSee('Instinct (Spellcast)')
         ->assertSee('This trait affects your divine sight and surveillance');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('seraph winged sentinel spellcast trait validation', function () {
    // Test that Winged Sentinel correctly assigns Instinct as spellcast trait
    // (different from Divine Wielder which uses Strength)
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Seraph', 10)
         ->click('Seraph')
         ->click('Next')
         ->waitForText('Winged Sentinel', 5)
         ->click('Winged Sentinel');
    
    // Verify spellcast trait is displayed
    $page->assertSee('Spellcast Trait: Instinct')
         ->assertSee('Your divine magic uses Instinct')
         ->assertSee('perception and surveillance');
    
    // Navigate to trait assignment to verify integration
    for ($i = 0; $i < 3; $i++) { // Skip ancestry, community
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Assign Your Traits', 5);
    
    // Verify Instinct is highlighted as spellcast trait
    $page->assertSee('Instinct (Spellcast)')
         ->assertSee('This is your primary divine perception trait');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
