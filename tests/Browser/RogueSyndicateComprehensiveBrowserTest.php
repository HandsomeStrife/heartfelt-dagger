<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive Pest 4 browser test for Rogue + Syndicate character creation workflow
 * 
 * This test validates the complete character creation process through UI interactions:
 * - Step 1: Class selection (Rogue)
 * - Step 2: Subclass selection (Syndicate)
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
 * Rogue + Syndicate specific validations:
 * - Rogue base stats (Hit Points: 4, Evasion: 10, Stress: 6)
 * - Syndicate uses Presence as spellcast trait
 * - Criminal Network: contacts and information gathering
 * - Underworld Connections: faction relationships and favors
 * - Street Smarts: urban navigation and social manipulation
 * - Fence Operations: item trafficking and black market access
 * - Gang Leadership: coordinating criminal activities
 * - Information Broker: knowledge trading and intelligence networks
 * - Marked for Death class feature integration with syndicate operations
 */

test('rogue syndicate complete character creation workflow', function () {
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
         ->waitForText('Syndicate', 5);
    
    // Step 2: Select Syndicate subclass
    $page->click('Syndicate')
         ->waitForText('Syndicate', 5)
         ->assertSee('Criminal Network')
         ->assertSee('contacts throughout the underworld')
         ->assertSee('gather information')
         ->assertSee('call in favors')
         ->assertSee('Underworld Connections')
         ->assertSee('relationship with criminal factions')
         ->assertSee('negotiate with gangs')
         ->assertSee('access to black markets')
         ->assertSee('Street Smarts')
         ->assertSee('navigate urban environments')
         ->assertSee('blend into crowds')
         ->assertSee('social manipulation')
         ->assertSee('Fence Operations')
         ->assertSee('traffic stolen goods')
         ->assertSee('launder money')
         ->assertSee('black market connections')
         ->assertSee('Gang Leadership')
         ->assertSee('coordinate criminal activities')
         ->assertSee('lead criminal operations')
         ->assertSee('Information Broker')
         ->assertSee('trade in secrets and knowledge')
         ->assertSee('intelligence networks')
         ->assertSee('Spellcast Trait: Presence');
    
    // Navigate to ancestry selection
    $page->click('Next')
         ->waitForText('Choose Your Ancestry', 5);
    
    // Step 3: Select Human ancestry (versatile for urban criminal)
    $page->click('Human')
         ->waitForText('Human', 5)
         ->assertSee('Versatile and adaptable')
         ->assertSee('+2 Stress');
    
    // Navigate to community selection
    $page->click('Next')
         ->waitForText('Choose Your Community', 5);
    
    // Step 4: Select Slyborne community (urban, perfect for syndicate)
    $page->click('Slyborne')
         ->waitForText('Slyborne', 5)
         ->assertSee('Urban')
         ->assertSee('cunning')
         ->assertSee('street-smart');
    
    // Navigate to trait assignment
    $page->click('Next')
         ->waitForText('Assign Your Traits', 5);
    
    // Step 5: Assign traits with exact DaggerHeart distribution (-1,0,0,+1,+1,+2)
    // Focus on Presence (spellcast trait) and social manipulation capabilities
    $page->click('Agility: +1')   // +1 (urban mobility)
         ->wait(0.5)
         ->click('Strength: -1')  // -1 (less physical focused)
         ->wait(0.5)
         ->click('Finesse: +1')   // +1 (precise criminal work)
         ->wait(0.5)
         ->click('Instinct: 0')   // 0
         ->wait(0.5)
         ->click('Presence: +2')  // +2 (primary spellcast trait)
         ->wait(0.5)
         ->click('Knowledge: 0')  // 0
         ->waitForText('Trait assignment complete!', 3);
    
    // Navigate to character info section
    $page->click('Next')
         ->waitForText('Character Information', 5);
    
    // Step 6: Verify character stats are calculated correctly
    // Rogue base: 4 HP, 10 Evasion, 6 Stress
    // Human ancestry: +2 Stress
    // Agility +1 affects evasion
    $page->assertSee('Hit Points: 4')  // Rogue base
         ->assertSee('Evasion: 11')    // 10 base + 1 agility
         ->assertSee('Stress: 8')      // 6 base + 2 human
         ->assertSee('Spellcast Trait: Presence (+2)');
    
    // Fill in character name
    $page->type('#character-name', 'Velvet Whisperknife')
         ->type('#character-pronouns', 'she/her')
         ->waitForText('Character details saved', 3);
    
    // Navigate to equipment selection
    $page->click('Next')
         ->waitForText('Choose Your Equipment', 5);
    
    // Step 7: Select appropriate equipment for syndicate rogue
    $page->click('Concealed Weapons') // Urban weapons
         ->wait(0.5)
         ->click('Fine Clothes')      // Social disguise
         ->wait(0.5)
         ->click('Thieves\' Tools')   // Criminal equipment
         ->waitForText('Equipment selection complete!', 3);
    
    // Navigate to background questions
    $page->click('Next')
         ->waitForText('Background Questions', 5);
    
    // Step 8: Answer rogue-specific background questions
    $page->type('#background-0', 'I rose through the ranks of the Crimson Daggers syndicate by turning rivals against each other instead of using brute force.')
         ->wait(0.5)
         ->type('#background-1', 'A war between criminal families has erupted, and I must choose sides or risk being crushed between them.')
         ->wait(0.5)
         ->type('#background-2', 'I fear that my network of contacts and allies will discover I\'ve been secretly feeding information to the city watch.')
         ->waitForText('Background complete!', 3);
    
    // Navigate to experiences
    $page->click('Next')
         ->waitForText('Choose Your Experiences', 5);
    
    // Step 9: Create two experiences
    $page->type('#experience-0', 'Black Market Trading')
         ->wait(0.5)
         ->type('#experience-1', 'Urban Navigation')
         ->waitForText('Experiences complete!', 3);
    
    // Navigate to domain cards
    $page->click('Next')
         ->waitForText('Choose Domain Cards', 5);
    
    // Step 10: Select domain cards from Midnight and Grace domains
    $page->assertSee('Available Domains: Midnight, Grace')
         ->click('Charm Person')      // Level 1 Grace card
         ->wait(0.5)
         ->click('Disguise')          // Level 1 Midnight card
         ->waitForText('Domain cards selected!', 3)
         ->assertSee('2/2 cards selected');
    
    // Navigate to connections
    $page->click('Next')
         ->waitForText('Character Connections', 5);
    
    // Step 11: Answer connection questions
    $page->type('#connection-0', 'You helped me escape the city guard during a heist gone wrong, and now I owe you my freedom.')
         ->wait(0.5)
         ->type('#connection-1', 'I know about your secret past because my information network is vast, but I\'ve kept your secrets safe.')
         ->wait(0.5)
         ->type('#connection-2', 'We worked together to bring down a corrupt noble, using my criminal contacts and your legitimate connections.')
         ->waitForText('Connections complete!', 3);
    
    // Navigate to final character sheet
    $page->click('Finish Character')
         ->waitForText('Velvet Whisperknife', 5);
    
    // Final validation: Verify complete character sheet
    $page->assertSee('Velvet Whisperknife')
         ->assertSee('Rogue')
         ->assertSee('Syndicate')
         ->assertSee('Human')
         ->assertSee('Slyborne')
         ->assertSee('Hit Points: 4')
         ->assertSee('Evasion: 11')
         ->assertSee('Stress: 8')
         ->assertSee('Agility: +1')
         ->assertSee('Strength: -1')
         ->assertSee('Finesse: +1')
         ->assertSee('Instinct: 0')
         ->assertSee('Presence: +2')
         ->assertSee('Knowledge: 0')
         ->assertSee('Marked for Death')
         ->assertSee('Criminal Network')
         ->assertSee('Underworld Connections')
         ->assertSee('Street Smarts')
         ->assertSee('Fence Operations')
         ->assertSee('Gang Leadership')
         ->assertSee('Information Broker')
         ->assertSee('Charm Person')
         ->assertSee('Disguise')
         ->assertSee('Concealed Weapons')
         ->assertSee('Fine Clothes')
         ->assertSee('Black Market Trading')
         ->assertSee('Urban Navigation');
    
    // Verify navigation to character viewer works
    $page->click('View Character')
         ->waitForText('Velvet Whisperknife', 5)
         ->assertSee('Rogue • Syndicate')
         ->assertSee('Human Slyborne');
    
    // Extract character key from URL to verify database persistence
    $currentUrl = $page->driver->getCurrentURL();
    preg_match('/character-viewer\/([A-Z0-9]{8,})/', $currentUrl, $matches);
    expect($matches)->toHaveCount(2);
    
    $characterKey = $matches[1];
    
    // Verify character was saved correctly in database
    $character = Character::where('character_key', $characterKey)->first();
    expect($character)->not()->toBeNull()
        ->class->toBe('rogue')
        ->subclass->toBe('syndicate')
        ->ancestry->toBe('human')
        ->community->toBe('slyborne')
        ->name->toBe('Velvet Whisperknife')
        ->pronouns->toBe('she/her');
    
    // Verify traits were saved with correct distribution
    $traits = $character->traits()->pluck('trait_value', 'trait_name')->toArray();
    expect($traits)->toBe([
        'agility' => 1,
        'strength' => -1,
        'finesse' => 1,
        'instinct' => 0,
        'presence' => 2,
        'knowledge' => 0,
    ]);
    
    // Verify character stats calculation
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(4);  // Rogue base
    expect($stats->evasion)->toBe(11);    // 10 base + 1 agility
    expect($stats->stress)->toBe(8);      // 6 base + 2 human
    
    // Verify spellcast trait is correct
    expect($character->getSpellcastTrait())->toBe('presence');
    expect($character->getSpellcastTraitValue())->toBe(2);
    
    // Verify domain cards were saved
    $domainCards = $character->domainCards()->pluck('card_name')->toArray();
    expect($domainCards)->toContain('Charm Person')
                       ->toContain('Disguise');
    
    // Verify experiences were saved
    $experiences = $character->experiences()->pluck('experience_name')->toArray();
    expect($experiences)->toContain('Black Market Trading')
                       ->toContain('Urban Navigation');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('rogue syndicate criminal network mechanics validation', function () {
    // Test that Syndicate's criminal network and contact systems are properly displayed
    // and favor/information mechanics are explained
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Rogue', 10)
         ->click('Rogue')
         ->click('Next')
         ->waitForText('Syndicate', 5)
         ->click('Syndicate');
    
    // Verify criminal network mechanics
    $page->assertSee('Criminal Network')
         ->assertSee('contacts throughout the underworld')
         ->assertSee('gather information')
         ->assertSee('call in favors')
         ->assertSee('network of criminals')
         ->assertSee('informants');
    
    // Verify faction relationships
    $page->assertSee('Underworld Connections')
         ->assertSee('relationship with criminal factions')
         ->assertSee('negotiate with gangs')
         ->assertSee('access to black markets')
         ->assertSee('criminal organizations');
    
    // Verify information trading
    $page->assertSee('Information Broker')
         ->assertSee('trade in secrets and knowledge')
         ->assertSee('intelligence networks')
         ->assertSee('sell information')
         ->assertSee('buy secrets');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('rogue syndicate urban operations validation', function () {
    // Test that Syndicate's urban focus and social manipulation are displayed
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Rogue', 10)
         ->click('Rogue')
         ->click('Next')
         ->waitForText('Syndicate', 5)
         ->click('Syndicate');
    
    // Verify urban expertise
    $page->assertSee('Street Smarts')
         ->assertSee('navigate urban environments')
         ->assertSee('blend into crowds')
         ->assertSee('social manipulation')
         ->assertSee('city knowledge');
    
    // Verify criminal operations
    $page->assertSee('Fence Operations')
         ->assertSee('traffic stolen goods')
         ->assertSee('launder money')
         ->assertSee('black market connections')
         ->assertSee('illegal trade');
    
    $page->assertSee('Gang Leadership')
         ->assertSee('coordinate criminal activities')
         ->assertSee('lead criminal operations')
         ->assertSee('organize heists')
         ->assertSee('manage underlings');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('rogue syndicate marked for death integration validation', function () {
    // Test that Syndicate features integrate with Rogue's Marked for Death
    
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
         ->waitForText('Syndicate', 5)
         ->click('Syndicate');
    
    // Verify Syndicate complements Marked for Death with network resources
    $page->assertSee('Criminal Network')     // Information gathering for targets
         ->assertSee('Information Broker')   // Intelligence on marked targets
         ->assertSee('Gang Leadership');     // Coordinated attacks on marked enemies
    
    // The synergy: Marked for Death provides damage scaling,
    // Syndicate provides intelligence, resources, and coordination for eliminating targets
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('rogue syndicate social manipulation validation', function () {
    // Test that Syndicate's social focus contrasts with stealth-focused subclasses
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Rogue', 10)
         ->click('Rogue')
         ->click('Next')
         ->waitForText('Syndicate', 5)
         ->click('Syndicate');
    
    // Verify social-focused abilities
    $page->assertSee('Street Smarts')
         ->assertSee('social manipulation')
         ->assertSee('blend into crowds')
         ->assertSee('charm and deceive');
    
    $page->assertSee('Underworld Connections')
         ->assertSee('negotiate with gangs')
         ->assertSee('diplomatic relations')
         ->assertSee('faction standing');
    
    // This is more about social infiltration and manipulation
    // rather than stealth and shadow magic like Nightwalker
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('rogue syndicate domain access verification', function () {
    // Test that Rogue class provides correct domain access (Midnight + Grace)
    // and Syndicate doesn't modify domain access
    
    $page = visit('/character-builder');
    
    // Navigate to domain cards step
    $page->waitForText('Rogue', 10)
         ->click('Rogue')
         ->click('Next')
         ->waitForText('Syndicate', 5)
         ->click('Syndicate');
    
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
    $page->assertPresent('[data-testid="domain-card-charm-person"]')
         ->assertPresent('[data-testid="domain-card-disguise"]');
    
    // Verify domain cards from other domains are NOT available
    $page->assertDontSee('Arcana Domain')
         ->assertDontSee('Blade Domain')
         ->assertDontSee('Valor Domain')
         ->assertDontSee('Bone Domain');
    
    // Verify can select exactly 2 cards
    $page->click('Charm Person')
         ->wait(0.5)
         ->assertSee('1/2 cards selected')
         ->click('Disguise')
         ->wait(0.5)
         ->assertSee('2/2 cards selected');
    
    // Verify cannot select a third card (buttons should be disabled)
    $remainingCards = $page->elements('[data-testid*="domain-card-"]:not([data-selected])');
    foreach ($remainingCards as $card) {
        expect($card->getAttribute('disabled'))->toBe('true');
    }
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('rogue syndicate presence scaling validation', function () {
    // Test that Syndicate's Presence-based abilities are properly explained
    // Social manipulation and criminal leadership scaling
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Rogue', 10)
         ->click('Rogue')
         ->click('Next')
         ->waitForText('Syndicate', 5)
         ->click('Syndicate');
    
    // Verify Presence scaling is clearly explained
    $page->assertSee('Criminal Network')
         ->assertSee('contacts scale with your Presence')
         ->assertSee('charisma affects network size');
    
    $page->assertSee('Gang Leadership')
         ->assertSee('leadership ability based on Presence')
         ->assertSee('commanding criminal operations');
    
    // Verify spellcast trait connection
    $page->assertSee('Spellcast Trait: Presence')
         ->assertSee('Your criminal magic uses Presence');
    
    // Navigate to trait assignment to verify integration
    for ($i = 0; $i < 3; $i++) { // Skip ancestry, community
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Assign Your Traits', 5);
    
    // Verify Presence is highlighted as spellcast trait
    $page->assertSee('Presence (Spellcast)')
         ->assertSee('This trait affects your criminal leadership and social manipulation');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('rogue syndicate spellcast trait validation', function () {
    // Test that Syndicate correctly assigns Presence as spellcast trait
    // (different from Nightwalker which uses Finesse)
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Rogue', 10)
         ->click('Rogue')
         ->click('Next')
         ->waitForText('Syndicate', 5)
         ->click('Syndicate');
    
    // Verify spellcast trait is displayed
    $page->assertSee('Spellcast Trait: Presence')
         ->assertSee('Your criminal magic uses Presence')
         ->assertSee('social manipulation and leadership');
    
    // Navigate to trait assignment to verify integration
    for ($i = 0; $i < 3; $i++) { // Skip ancestry, community
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Assign Your Traits', 5);
    
    // Verify Presence is highlighted as spellcast trait
    $page->assertSee('Presence (Spellcast)')
         ->assertSee('This is your primary criminal leadership trait');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
