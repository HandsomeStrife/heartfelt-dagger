<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive Pest 4 browser test for Ranger + Wayfinder character creation workflow
 * 
 * This test validates the complete character creation process through UI interactions:
 * - Step 1: Class selection (Ranger)
 * - Step 2: Subclass selection (Wayfinder)
 * - Step 3: Ancestry selection  
 * - Step 4: Community selection
 * - Step 5: Trait assignment (validates exact -1,0,0,+1,+1,+2 distribution)
 * - Step 6: Character stats verification
 * - Step 7: Equipment selection
 * - Step 8: Background questions
 * - Step 9: Experiences
 * - Step 10: Domain cards (Sage + Bone access)
 * - Step 11: Connections
 * 
 * Ranger + Wayfinder specific validations:
 * - Ranger base stats (Hit Points: 5, Evasion: 9, Stress: 6)
 * - Wayfinder uses Agility as spellcast trait
 * - Pathfinding: terrain navigation and movement enhancement
 * - Mark Territory: area control and environmental awareness
 * - Swift Travel: enhanced movement and party guidance
 * - Terrain Mastery: environmental adaptation and benefits
 * - Guide's Intuition: navigation and danger sense enhancement
 * - Master Navigator: ultimate exploration and travel abilities
 * - Hunter's Mark class feature integration with tracking focus
 */

test('ranger wayfinder complete character creation workflow', function () {
    // Start at character builder main page
    $page = visit('/character-builder');
    
    // Verify initial page load
    $page->assertSee('Character Builder')
         ->waitForText('Ranger', 10);
    
    // Step 1: Select Ranger class
    $page->click('Ranger')
         ->waitForText('Ranger', 5)
         ->assertSee('Sage')
         ->assertSee('Bone')
         ->assertSee('Starting Evasion: 9')
         ->assertSee('Starting Hit Points: 5')
         ->assertSee('Hunter\'s Mark')
         ->assertSee('Colossus Slayer')
         ->assertSee('Natural Explorer')
         ->assertSee('wilderness survival and tracking');
    
    // Navigate to subclass selection
    $page->click('Next')
         ->waitForText('Wayfinder', 5);
    
    // Step 2: Select Wayfinder subclass
    $page->click('Wayfinder')
         ->waitForText('Wayfinder', 5)
         ->assertSee('Pathfinding')
         ->assertSee('navigate difficult terrain')
         ->assertSee('ignore movement penalties')
         ->assertSee('lead others through hazardous paths')
         ->assertSee('Mark Territory')
         ->assertSee('claim an area as your domain')
         ->assertSee('enhanced awareness within marked territory')
         ->assertSee('sense intruders and changes')
         ->assertSee('Swift Travel')
         ->assertSee('enhanced movement speed')
         ->assertSee('guide your party faster')
         ->assertSee('long-distance travel')
         ->assertSee('Terrain Mastery')
         ->assertSee('adapt to any environment')
         ->assertSee('gain benefits from terrain types')
         ->assertSee('Guide\'s Intuition')
         ->assertSee('sense danger before it strikes')
         ->assertSee('predict weather and hazards')
         ->assertSee('Master Navigator')
         ->assertSee('never lose your way')
         ->assertSee('find hidden paths and shortcuts')
         ->assertSee('Spellcast Trait: Agility');
    
    // Navigate to ancestry selection
    $page->click('Next')
         ->waitForText('Choose Your Ancestry', 5);
    
    // Step 3: Select Elf ancestry (natural navigation and keen senses)
    $page->click('Elf')
         ->waitForText('Elf', 5)
         ->assertSee('Graceful beings')
         ->assertSee('keen senses');
    
    // Navigate to community selection
    $page->click('Next')
         ->waitForText('Choose Your Community', 5);
    
    // Step 4: Select Wanderborne community (perfect for wayfinder)
    $page->click('Wanderborne')
         ->waitForText('Wanderborne', 5)
         ->assertSee('Nomadic')
         ->assertSee('traveling')
         ->assertSee('adaptable');
    
    // Navigate to trait assignment
    $page->click('Next')
         ->waitForText('Assign Your Traits', 5);
    
    // Step 5: Assign traits with exact DaggerHeart distribution (-1,0,0,+1,+1,+2)
    // Focus on Agility (spellcast trait) and navigation capabilities
    $page->click('Agility: +2')   // +2 (primary spellcast trait)
         ->wait(0.5)
         ->click('Strength: 0')   // 0
         ->wait(0.5)
         ->click('Finesse: +1')   // +1 (precision and stealth)
         ->wait(0.5)
         ->click('Instinct: +1')  // +1 (awareness and tracking)
         ->wait(0.5)
         ->click('Presence: 0')   // 0
         ->wait(0.5)
         ->click('Knowledge: -1') // -1 (less academic focus)
         ->waitForText('Trait assignment complete!', 3);
    
    // Navigate to character info section
    $page->click('Next')
         ->waitForText('Character Information', 5);
    
    // Step 6: Verify character stats are calculated correctly
    // Ranger base: 5 HP, 9 Evasion, 6 Stress
    // Agility +2 affects evasion
    $page->assertSee('Hit Points: 5')  // Ranger base
         ->assertSee('Evasion: 11')    // 9 base + 2 agility
         ->assertSee('Stress: 6')      // Ranger base
         ->assertSee('Spellcast Trait: Agility (+2)');
    
    // Fill in character name
    $page->type('#character-name', 'Swiftfoot Trailblazer')
         ->type('#character-pronouns', 'she/her')
         ->waitForText('Character details saved', 3);
    
    // Navigate to equipment selection
    $page->click('Next')
         ->waitForText('Choose Your Equipment', 5);
    
    // Step 7: Select appropriate equipment for wayfinder ranger
    $page->click('Longbow')           // Ranged weapon for scouting
         ->wait(0.5)
         ->click('Leather Armor')     // Light armor for mobility
         ->wait(0.5)
         ->click('Explorer\'s Pack')  // Navigation tools
         ->waitForText('Equipment selection complete!', 3);
    
    // Navigate to background questions
    $page->click('Next')
         ->waitForText('Background Questions', 5);
    
    // Step 8: Answer ranger-specific background questions
    $page->type('#background-0', 'I grew up leading caravans through the most dangerous wilderness routes, learning every secret path and hidden danger.')
         ->wait(0.5)
         ->type('#background-1', 'A great earthquake has shifted the landscape, and all the old maps are useless - only I know how to navigate the new terrain.')
         ->wait(0.5)
         ->type('#background-2', 'I fear that my wanderlust will prevent me from ever truly settling down or forming lasting relationships.')
         ->waitForText('Background complete!', 3);
    
    // Navigate to experiences
    $page->click('Next')
         ->waitForText('Choose Your Experiences', 5);
    
    // Step 9: Create two experiences
    $page->type('#experience-0', 'Cartography')
         ->wait(0.5)
         ->type('#experience-1', 'Wilderness Survival')
         ->waitForText('Experiences complete!', 3);
    
    // Navigate to domain cards
    $page->click('Next')
         ->waitForText('Choose Domain Cards', 5);
    
    // Step 10: Select domain cards from Sage and Bone domains
    $page->assertSee('Available Domains: Sage, Bone')
         ->click('Track')             // Level 1 Sage card
         ->wait(0.5)
         ->click('Intimidate')        // Level 1 Bone card
         ->waitForText('Domain cards selected!', 3)
         ->assertSee('2/2 cards selected');
    
    // Navigate to connections
    $page->click('Next')
         ->waitForText('Character Connections', 5);
    
    // Step 11: Answer connection questions
    $page->type('#connection-0', 'I guided you through a treacherous mountain pass during a blizzard, and now you trust my judgment completely.')
         ->wait(0.5)
         ->type('#connection-1', 'You saved me from bandits when I was lost and alone, proving that even the best navigator needs friends.')
         ->wait(0.5)
         ->type('#connection-2', 'We discovered an ancient ruin together that doesn\'t appear on any map, and we\'ve sworn to keep its location secret.')
         ->waitForText('Connections complete!', 3);
    
    // Navigate to final character sheet
    $page->click('Finish Character')
         ->waitForText('Swiftfoot Trailblazer', 5);
    
    // Final validation: Verify complete character sheet
    $page->assertSee('Swiftfoot Trailblazer')
         ->assertSee('Ranger')
         ->assertSee('Wayfinder')
         ->assertSee('Elf')
         ->assertSee('Wanderborne')
         ->assertSee('Hit Points: 5')
         ->assertSee('Evasion: 11')
         ->assertSee('Stress: 6')
         ->assertSee('Agility: +2')
         ->assertSee('Strength: 0')
         ->assertSee('Finesse: +1')
         ->assertSee('Instinct: +1')
         ->assertSee('Presence: 0')
         ->assertSee('Knowledge: -1')
         ->assertSee('Hunter\'s Mark')
         ->assertSee('Colossus Slayer')
         ->assertSee('Natural Explorer')
         ->assertSee('Pathfinding')
         ->assertSee('Mark Territory')
         ->assertSee('Swift Travel')
         ->assertSee('Terrain Mastery')
         ->assertSee('Guide\'s Intuition')
         ->assertSee('Master Navigator')
         ->assertSee('Track')
         ->assertSee('Intimidate')
         ->assertSee('Longbow')
         ->assertSee('Leather Armor')
         ->assertSee('Cartography')
         ->assertSee('Wilderness Survival');
    
    // Verify navigation to character viewer works
    $page->click('View Character')
         ->waitForText('Swiftfoot Trailblazer', 5)
         ->assertSee('Ranger • Wayfinder')
         ->assertSee('Elf Wanderborne');
    
    // Extract character key from URL to verify database persistence
    $currentUrl = $page->driver->getCurrentURL();
    preg_match('/character-viewer\/([A-Z0-9]{8,})/', $currentUrl, $matches);
    expect($matches)->toHaveCount(2);
    
    $characterKey = $matches[1];
    
    // Verify character was saved correctly in database
    $character = Character::where('character_key', $characterKey)->first();
    expect($character)->not()->toBeNull()
        ->class->toBe('ranger')
        ->subclass->toBe('wayfinder')
        ->ancestry->toBe('elf')
        ->community->toBe('wanderborne')
        ->name->toBe('Swiftfoot Trailblazer')
        ->pronouns->toBe('she/her');
    
    // Verify traits were saved with correct distribution
    $traits = $character->traits()->pluck('trait_value', 'trait_name')->toArray();
    expect($traits)->toBe([
        'agility' => 2,
        'strength' => 0,
        'finesse' => 1,
        'instinct' => 1,
        'presence' => 0,
        'knowledge' => -1,
    ]);
    
    // Verify character stats calculation
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(5);  // Ranger base
    expect($stats->evasion)->toBe(11);    // 9 base + 2 agility
    expect($stats->stress)->toBe(6);      // Ranger base
    
    // Verify spellcast trait is correct
    expect($character->getSpellcastTrait())->toBe('agility');
    expect($character->getSpellcastTraitValue())->toBe(2);
    
    // Verify domain cards were saved
    $domainCards = $character->domainCards()->pluck('card_name')->toArray();
    expect($domainCards)->toContain('Track')
                       ->toContain('Intimidate');
    
    // Verify experiences were saved
    $experiences = $character->experiences()->pluck('experience_name')->toArray();
    expect($experiences)->toContain('Cartography')
                       ->toContain('Wilderness Survival');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('ranger wayfinder pathfinding mechanics validation', function () {
    // Test that Wayfinder's pathfinding and navigation mechanics are properly displayed
    // and terrain mastery benefits are explained
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Ranger', 10)
         ->click('Ranger')
         ->click('Next')
         ->waitForText('Wayfinder', 5)
         ->click('Wayfinder');
    
    // Verify pathfinding mechanics
    $page->assertSee('Pathfinding')
         ->assertSee('navigate difficult terrain')
         ->assertSee('ignore movement penalties')
         ->assertSee('lead others through hazardous paths')
         ->assertSee('find the safest and fastest routes');
    
    // Verify terrain mastery
    $page->assertSee('Terrain Mastery')
         ->assertSee('adapt to any environment')
         ->assertSee('gain benefits from terrain types')
         ->assertSee('forest')
         ->assertSee('mountains')
         ->assertSee('swamps')
         ->assertSee('deserts');
    
    // Verify travel enhancement
    $page->assertSee('Swift Travel')
         ->assertSee('enhanced movement speed')
         ->assertSee('guide your party faster')
         ->assertSee('long-distance travel')
         ->assertSee('reduce travel time');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('ranger wayfinder territory mechanics validation', function () {
    // Test that Wayfinder's territory marking and area control abilities are displayed
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Ranger', 10)
         ->click('Ranger')
         ->click('Next')
         ->waitForText('Wayfinder', 5)
         ->click('Wayfinder');
    
    // Verify territory control features
    $page->assertSee('Mark Territory')
         ->assertSee('claim an area as your domain')
         ->assertSee('enhanced awareness within marked territory')
         ->assertSee('sense intruders and changes')
         ->assertSee('environmental control');
    
    // Verify enhanced navigation
    $page->assertSee('Guide\'s Intuition')
         ->assertSee('sense danger before it strikes')
         ->assertSee('predict weather and hazards')
         ->assertSee('anticipate environmental threats');
    
    $page->assertSee('Master Navigator')
         ->assertSee('never lose your way')
         ->assertSee('find hidden paths and shortcuts')
         ->assertSee('navigate by stars')
         ->assertSee('read natural signs');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('ranger wayfinder hunters mark integration validation', function () {
    // Test that Wayfinder features integrate with Ranger's Hunter's Mark
    
    $page = visit('/character-builder');
    
    // Navigate through class selection
    $page->waitForText('Ranger', 10)
         ->click('Ranger');
    
    // Verify Ranger's Hunter's Mark features are shown
    $page->assertSee('Hunter\'s Mark')
         ->assertSee('mark a target for tracking')
         ->assertSee('enhanced damage against marked targets')
         ->assertSee('Colossus Slayer')
         ->assertSee('massive creatures')
         ->assertSee('Natural Explorer')
         ->assertSee('wilderness expertise');
    
    // Continue to subclass
    $page->click('Next')
         ->waitForText('Wayfinder', 5)
         ->click('Wayfinder');
    
    // Verify Wayfinder enhances tracking with navigation focus
    $page->assertSee('Pathfinding')       // Enhanced movement for pursuit
         ->assertSee('Mark Territory')    // Area control beyond single target
         ->assertSee('Guide\'s Intuition'); // Danger sense for hunting
    
    // The synergy: Hunter's Mark for single target tracking,
    // Wayfinder provides area control and enhanced pursuit capabilities
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('ranger wayfinder natural explorer integration validation', function () {
    // Test that Wayfinder features enhance Ranger's wilderness exploration
    
    $page = visit('/character-builder');
    
    // Navigate through class selection
    $page->waitForText('Ranger', 10)
         ->click('Ranger');
    
    // Verify Ranger's exploration features
    $page->assertSee('Natural Explorer')
         ->assertSee('wilderness expertise')
         ->assertSee('survival skills')
         ->assertSee('environmental knowledge');
    
    // Continue to subclass
    $page->click('Next')
         ->waitForText('Wayfinder', 5)
         ->click('Wayfinder');
    
    // Verify Wayfinder expands exploration capabilities
    $page->assertSee('Terrain Mastery')   // Enhanced environmental adaptation
         ->assertSee('Swift Travel')      // Improved movement through wilderness
         ->assertSee('Master Navigator'); // Ultimate exploration mastery
    
    // The synergy: Natural Explorer provides baseline wilderness skills,
    // Wayfinder specializes in navigation, speed, and terrain mastery
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('ranger wayfinder domain access verification', function () {
    // Test that Ranger class provides correct domain access (Sage + Bone)
    // and Wayfinder doesn't modify domain access
    
    $page = visit('/character-builder');
    
    // Navigate to domain cards step
    $page->waitForText('Ranger', 10)
         ->click('Ranger')
         ->click('Next')
         ->waitForText('Wayfinder', 5)
         ->click('Wayfinder');
    
    // Skip to domain cards section
    for ($i = 0; $i < 7; $i++) { // Skip ancestry, community, traits, info, equipment, background, experiences
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Choose Domain Cards', 5);
    
    // Verify correct domains are available
    $page->assertSee('Available Domains: Sage, Bone')
         ->assertSee('Sage Domain')
         ->assertSee('Bone Domain');
    
    // Verify level 1 cards from both domains are available
    $page->assertPresent('[data-testid="domain-card-track"]')
         ->assertPresent('[data-testid="domain-card-intimidate"]');
    
    // Verify domain cards from other domains are NOT available
    $page->assertDontSee('Arcana Domain')
         ->assertDontSee('Valor Domain')
         ->assertDontSee('Grace Domain')
         ->assertDontSee('Midnight Domain');
    
    // Verify can select exactly 2 cards
    $page->click('Track')
         ->wait(0.5)
         ->assertSee('1/2 cards selected')
         ->click('Intimidate')
         ->wait(0.5)
         ->assertSee('2/2 cards selected');
    
    // Verify cannot select a third card (buttons should be disabled)
    $remainingCards = $page->elements('[data-testid*="domain-card-"]:not([data-selected])');
    foreach ($remainingCards as $card) {
        expect($card->getAttribute('disabled'))->toBe('true');
    }
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('ranger wayfinder agility scaling validation', function () {
    // Test that Wayfinder's Agility-based abilities are properly explained
    // Movement and navigation speed scaling with Agility
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Ranger', 10)
         ->click('Ranger')
         ->click('Next')
         ->waitForText('Wayfinder', 5)
         ->click('Wayfinder');
    
    // Verify Agility scaling is clearly explained
    $page->assertSee('Swift Travel')
         ->assertSee('movement speed scales with Agility')
         ->assertSee('enhanced by your nimbleness');
    
    $page->assertSee('Pathfinding')
         ->assertSee('navigation speed affected by Agility')
         ->assertSee('quicker route finding');
    
    // Verify spellcast trait connection
    $page->assertSee('Spellcast Trait: Agility')
         ->assertSee('Your wayfinding magic uses Agility');
    
    // Navigate to trait assignment to verify integration
    for ($i = 0; $i < 3; $i++) { // Skip ancestry, community
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Assign Your Traits', 5);
    
    // Verify Agility is highlighted as spellcast trait
    $page->assertSee('Agility (Spellcast)')
         ->assertSee('This trait affects your movement and navigation magic');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('ranger wayfinder spellcast trait validation', function () {
    // Test that Wayfinder correctly assigns Agility as spellcast trait
    // (different from Beastbound which might use different trait)
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Ranger', 10)
         ->click('Ranger')
         ->click('Next')
         ->waitForText('Wayfinder', 5)
         ->click('Wayfinder');
    
    // Verify spellcast trait is displayed
    $page->assertSee('Spellcast Trait: Agility')
         ->assertSee('Your wayfinding magic uses Agility')
         ->assertSee('movement and navigation');
    
    // Navigate to trait assignment to verify integration
    for ($i = 0; $i < 3; $i++) { // Skip ancestry, community
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Assign Your Traits', 5);
    
    // Verify Agility is highlighted as spellcast trait
    $page->assertSee('Agility (Spellcast)')
         ->assertSee('This is your primary wayfinding trait');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
