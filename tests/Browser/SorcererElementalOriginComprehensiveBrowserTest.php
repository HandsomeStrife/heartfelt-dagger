<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive Pest 4 browser test for Sorcerer + Elemental Origin character creation workflow
 * 
 * This test validates the complete character creation process through UI interactions:
 * - Step 1: Class selection (Sorcerer)
 * - Step 2: Subclass selection (Elemental Origin)
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
 * Sorcerer + Elemental Origin specific validations:
 * - Sorcerer base stats (Hit Points: 6, Evasion: 10, Stress: 6)
 * - Elemental Origin uses Instinct as spellcast trait
 * - Elementalist feature: element selection and Hope-based bonuses
 * - Natural Evasion: stress-based d6 evasion bonus
 * - Transcendence: transformation benefits selection
 * - Volatile Magic Hope feature integration
 * - Arcane Sense and Minor Illusion class features
 */

test('sorcerer elemental origin complete character creation workflow', function () {
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
         ->assertSee('innate magic users');
    
    // Navigate to subclass selection
    $page->click('Next')
         ->waitForText('Elemental Origin', 5);
    
    // Step 2: Select Elemental Origin subclass
    $page->click('Elemental Origin')
         ->waitForText('Elemental Origin', 5)
         ->assertSee('Elementalist')
         ->assertSee('Choose one of the following elements')
         ->assertSee('air, earth, fire, lightning, water')
         ->assertSee('Natural Evasion')
         ->assertSee('roll a d6 and add its result to your Evasion')
         ->assertSee('Transcendence')
         ->assertSee('transform into a physical manifestation')
         ->assertSee('Spellcast Trait: Instinct');
    
    // Select elemental focus (fire for this test)
    $page->click('Fire Element')
         ->waitForText('Fire selected', 3);
    
    // Navigate to ancestry selection
    $page->click('Next')
         ->waitForText('Choose Your Ancestry', 5);
    
    // Step 3: Select Drakona ancestry (thematic for elemental sorcerer)
    $page->click('Drakona')
         ->waitForText('Drakona', 5)
         ->assertSee('Dragon-blooded')
         ->assertSee('elemental breath');
    
    // Navigate to community selection
    $page->click('Next')
         ->waitForText('Choose Your Community', 5);
    
    // Step 4: Select Wildborne community (nature connection fits elemental theme)
    $page->click('Wildborne')
         ->waitForText('Wildborne', 5)
         ->assertSee('Wilderness-dwelling')
         ->assertSee('survival-focused');
    
    // Navigate to trait assignment
    $page->click('Next')
         ->waitForText('Assign Your Traits', 5);
    
    // Step 5: Assign traits with exact DaggerHeart distribution (-1,0,0,+1,+1,+2)
    // Focus on Instinct (spellcast trait) and magical capabilities
    $page->click('Agility: +1')   // +1 (mobility)
         ->wait(0.5)
         ->click('Strength: -1')  // -1 (not physical focused)
         ->wait(0.5)
         ->click('Finesse: 0')    // 0
         ->wait(0.5)
         ->click('Instinct: +2')  // +2 (primary spellcast trait)
         ->wait(0.5)
         ->click('Presence: +1')  // +1 (magic presence)
         ->wait(0.5)
         ->click('Knowledge: 0')  // 0
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
    $page->type('#character-name', 'Ignis Flameweaver')
         ->type('#character-pronouns', 'they/them')
         ->waitForText('Character details saved', 3);
    
    // Navigate to equipment selection
    $page->click('Next')
         ->waitForText('Choose Your Equipment', 5);
    
    // Step 7: Select appropriate equipment for sorcerer
    $page->click('Staff')            // Spellcasting focus
         ->wait(0.5)
         ->click('Robes')            // Light armor for mobility
         ->wait(0.5)
         ->click('Arcane Components') // Spellcasting materials
         ->waitForText('Equipment selection complete!', 3);
    
    // Navigate to background questions
    $page->click('Next')
         ->waitForText('Background Questions', 5);
    
    // Step 8: Answer sorcerer-specific background questions
    $page->type('#background-0', 'I accidentally set the town square on fire during a moment of intense emotion, making the villagers fear my untamed magic.')
         ->wait(0.5)
         ->type('#background-1', 'An ancient dragon taught me to channel my elemental fury, but she vanished when dark forces began hunting elemental beings.')
         ->wait(0.5)
         ->type('#background-2', 'I fear losing control of my fire magic and harming innocent people, so I often hold back when I should act decisively.')
         ->waitForText('Background complete!', 3);
    
    // Navigate to experiences
    $page->click('Next')
         ->waitForText('Choose Your Experiences', 5);
    
    // Step 9: Create two experiences
    $page->type('#experience-0', 'Elemental Meditation')
         ->wait(0.5)
         ->type('#experience-1', 'Fire Dancing')
         ->waitForText('Experiences complete!', 3);
    
    // Navigate to domain cards
    $page->click('Next')
         ->waitForText('Choose Domain Cards', 5);
    
    // Step 10: Select domain cards from Arcana and Midnight domains
    $page->assertSee('Available Domains: Arcana, Midnight')
         ->click('Flame Jet')        // Level 1 Arcana card
         ->wait(0.5)
         ->click('Shadow Step')      // Level 1 Midnight card
         ->waitForText('Domain cards selected!', 3)
         ->assertSee('2/2 cards selected');
    
    // Navigate to connections
    $page->click('Next')
         ->waitForText('Character Connections', 5);
    
    // Step 11: Answer connection questions
    $page->type('#connection-0', 'You showed me unwavering trust even after seeing my magic accidentally destroy property, giving me confidence in my abilities.')
         ->wait(0.5)
         ->type('#connection-1', 'My reckless experimentation with fire magic once put you in danger, and I\'ve been cautious around you ever since.')
         ->wait(0.5)
         ->type('#connection-2', 'We both carry the secret that my magic is tied to an ancient dragon bloodline that certain factions would kill to control.')
         ->waitForText('Connections complete!', 3);
    
    // Navigate to final character sheet
    $page->click('Finish Character')
         ->waitForText('Ignis Flameweaver', 5);
    
    // Final validation: Verify complete character sheet
    $page->assertSee('Ignis Flameweaver')
         ->assertSee('Sorcerer')
         ->assertSee('Elemental Origin')
         ->assertSee('Drakona')
         ->assertSee('Wildborne')
         ->assertSee('Hit Points: 6')
         ->assertSee('Evasion: 11')
         ->assertSee('Stress: 6')
         ->assertSee('Agility: +1')
         ->assertSee('Strength: -1')
         ->assertSee('Finesse: 0')
         ->assertSee('Instinct: +2')
         ->assertSee('Presence: +1')
         ->assertSee('Knowledge: 0')
         ->assertSee('Elementalist: Fire')
         ->assertSee('Natural Evasion')
         ->assertSee('Transcendence')
         ->assertSee('Flame Jet')
         ->assertSee('Shadow Step')
         ->assertSee('Staff')
         ->assertSee('Robes')
         ->assertSee('Elemental Meditation')
         ->assertSee('Fire Dancing');
    
    // Verify navigation to character viewer works
    $page->click('View Character')
         ->waitForText('Ignis Flameweaver', 5)
         ->assertSee('Sorcerer • Elemental Origin')
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
        ->subclass->toBe('elemental origin')
        ->ancestry->toBe('drakona')
        ->community->toBe('wildborne')
        ->name->toBe('Ignis Flameweaver')
        ->pronouns->toBe('they/them');
    
    // Verify traits were saved with correct distribution
    $traits = $character->traits()->pluck('trait_value', 'trait_name')->toArray();
    expect($traits)->toBe([
        'agility' => 1,
        'strength' => -1,
        'finesse' => 0,
        'instinct' => 2,
        'presence' => 1,
        'knowledge' => 0,
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
    expect($domainCards)->toContain('Flame Jet')
                       ->toContain('Shadow Step');
    
    // Verify experiences were saved
    $experiences = $character->experiences()->pluck('experience_name')->toArray();
    expect($experiences)->toContain('Elemental Meditation')
                       ->toContain('Fire Dancing');
    
    // Verify elemental choice was saved
    expect($character->getSubclassChoices())->toContain('element:fire');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('sorcerer elemental origin element selection validation', function () {
    // Test that Elemental Origin properly presents element choices
    // and validates the selection process
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Sorcerer', 10)
         ->click('Sorcerer')
         ->click('Next')
         ->waitForText('Elemental Origin', 5)
         ->click('Elemental Origin');
    
    // Verify all five elements are available for selection
    $page->assertSee('Choose one of the following elements at character creation:')
         ->assertSee('Air')
         ->assertSee('Earth')
         ->assertSee('Fire')
         ->assertSee('Lightning')
         ->assertSee('Water');
    
    // Test selecting each element
    $page->click('Air Element')
         ->waitForText('Air selected', 2)
         ->assertSee('control over air')
         ->assertSee('wind effects');
    
    $page->click('Earth Element')
         ->waitForText('Earth selected', 2)
         ->assertSee('control over earth')
         ->assertSee('stone effects');
    
    $page->click('Fire Element')
         ->waitForText('Fire selected', 2)
         ->assertSee('control over fire')
         ->assertSee('flame effects');
    
    $page->click('Lightning Element')
         ->waitForText('Lightning selected', 2)
         ->assertSee('control over lightning')
         ->assertSee('electrical effects');
    
    $page->click('Water Element')
         ->waitForText('Water selected', 2)
         ->assertSee('control over water')
         ->assertSee('liquid effects');
    
    // Verify element mechanics description
    $page->assertSee('spend a Hope and describe how your control')
         ->assertSee('+2 bonus to the roll or a +3 bonus to the roll\'s damage');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('sorcerer elemental origin spellcast trait validation', function () {
    // Test that Elemental Origin correctly assigns Instinct as spellcast trait
    // (different from Primal Origin which also uses Instinct)
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Sorcerer', 10)
         ->click('Sorcerer')
         ->click('Next')
         ->waitForText('Elemental Origin', 5)
         ->click('Elemental Origin');
    
    // Verify spellcast trait is displayed
    $page->assertSee('Spellcast Trait: Instinct')
         ->assertSee('Your magical abilities use Instinct');
    
    // Navigate to trait assignment to verify integration
    for ($i = 0; $i < 3; $i++) { // Skip ancestry, community
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Assign Your Traits', 5);
    
    // Verify Instinct is highlighted as spellcast trait
    $page->assertSee('Instinct (Spellcast)')
         ->assertSee('This is your primary magical trait');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('sorcerer elemental origin domain access verification', function () {
    // Test that Sorcerer class provides correct domain access (Arcana + Midnight)
    // and Elemental Origin doesn't modify domain access
    
    $page = visit('/character-builder');
    
    // Navigate to domain cards step
    $page->waitForText('Sorcerer', 10)
         ->click('Sorcerer')
         ->click('Next')
         ->waitForText('Elemental Origin', 5)
         ->click('Elemental Origin');
    
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
    $page->assertPresent('[data-testid="domain-card-flame-jet"]')
         ->assertPresent('[data-testid="domain-card-shadow-step"]');
    
    // Verify domain cards from other domains are NOT available
    $page->assertDontSee('Valor Domain')
         ->assertDontSee('Blade Domain')
         ->assertDontSee('Grace Domain')
         ->assertDontSee('Splendor Domain');
    
    // Verify can select exactly 2 cards
    $page->click('Flame Jet')
         ->wait(0.5)
         ->assertSee('1/2 cards selected')
         ->click('Shadow Step')
         ->wait(0.5)
         ->assertSee('2/2 cards selected');
    
    // Verify cannot select a third card (buttons should be disabled)
    $remainingCards = $page->elements('[data-testid*="domain-card-"]:not([data-selected])');
    foreach ($remainingCards as $card) {
        expect($card->getAttribute('disabled'))->toBe('true');
    }
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('sorcerer elemental origin class features integration', function () {
    // Test that Sorcerer class features work with Elemental Origin
    // Volatile Magic + Elemental abilities should complement each other
    
    $page = visit('/character-builder');
    
    // Navigate through class selection
    $page->waitForText('Sorcerer', 10)
         ->click('Sorcerer');
    
    // Verify Sorcerer class features are shown
    $page->assertSee('Volatile Magic')
         ->assertSee('Spend 3 Hope')
         ->assertSee('reroll any number of damage dice')
         ->assertSee('magic damage')
         ->assertSee('Arcane Sense')
         ->assertSee('magical people and objects')
         ->assertSee('Minor Illusion')
         ->assertSee('visual illusion')
         ->assertSee('Channel Raw Power')
         ->assertSee('enhance a spell that deals damage');
    
    // Continue to subclass
    $page->click('Next')
         ->waitForText('Elemental Origin', 5)
         ->click('Elemental Origin');
    
    // Verify Elemental Origin features complement Sorcerer abilities
    $page->assertSee('Elementalist')     // Hope-based damage bonuses work with Volatile Magic
         ->assertSee('Natural Evasion')  // Defensive option for fragile sorcerer
         ->assertSee('Transcendence');   // Ultimate magical transformation
    
    // The synergy: Volatile Magic rerolls damage dice, Elementalist adds bonuses,
    // Natural Evasion provides defense, Transcendence enhances everything
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
