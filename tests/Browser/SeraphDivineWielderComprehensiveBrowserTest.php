<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive Pest 4 browser test for Seraph + Divine Wielder character creation workflow
 * 
 * This test validates the complete character creation process through UI interactions:
 * - Step 1: Class selection (Seraph)
 * - Step 2: Subclass selection (Divine Wielder)
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
 * Seraph + Divine Wielder specific validations:
 * - Seraph base stats (Hit Points: 7, Evasion: 9, Stress: 6)
 * - Divine Wielder uses Strength as spellcast trait
 * - Prayer Dice mechanics: session-based d4 resource pool
 * - Spirit Weapon: weapon throwing/returning mechanics with stress costs
 * - Sparing Touch: healing ability (once/twice per long rest)
 * - Devout: Prayer Dice enhancements and doubled Sparing Touch
 * - Sacred Resonance: matching die damage doubling
 * - Life Support Hope feature integration
 */

test('seraph divine wielder complete character creation workflow', function () {
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
         ->waitForText('Divine Wielder', 5);
    
    // Step 2: Select Divine Wielder subclass
    $page->click('Divine Wielder')
         ->waitForText('Divine Wielder', 5)
         ->assertSee('Spirit Weapon')
         ->assertSee('weapon with a range of Melee or Very Close')
         ->assertSee('fly from your hand to attack')
         ->assertSee('within Close range')
         ->assertSee('mark a Stress to target an additional adversary')
         ->assertSee('Sparing Touch')
         ->assertSee('once per long rest')
         ->assertSee('touch a creature and clear 2 Hit Points or 2 Stress')
         ->assertSee('Devout')
         ->assertSee('roll an additional die and discard the lowest result')
         ->assertSee('twice instead of once per long rest')
         ->assertSee('Sacred Resonance')
         ->assertSee('if any of the die results match')
         ->assertSee('double the value of each matching die')
         ->assertSee('Spellcast Trait: Strength');
    
    // Navigate to ancestry selection
    $page->click('Next')
         ->waitForText('Choose Your Ancestry', 5);
    
    // Step 3: Select Human ancestry (versatile for divine warrior)
    $page->click('Human')
         ->waitForText('Human', 5)
         ->assertSee('Versatile and adaptable')
         ->assertSee('+2 Stress');
    
    // Navigate to community selection
    $page->click('Next')
         ->waitForText('Choose Your Community', 5);
    
    // Step 4: Select Orderborne community (faith-based, perfect for seraph)
    $page->click('Orderborne')
         ->waitForText('Orderborne', 5)
         ->assertSee('Disciplined')
         ->assertSee('faith-based');
    
    // Navigate to trait assignment
    $page->click('Next')
         ->waitForText('Assign Your Traits', 5);
    
    // Step 5: Assign traits with exact DaggerHeart distribution (-1,0,0,+1,+1,+2)
    // Focus on Strength (spellcast trait) and divine warrior capabilities
    $page->click('Agility: 0')     // 0
         ->wait(0.5)
         ->click('Strength: +2')   // +2 (primary spellcast trait)
         ->wait(0.5)
         ->click('Finesse: +1')    // +1 (weapon precision)
         ->wait(0.5)
         ->click('Instinct: +1')   // +1 (divine guidance)
         ->wait(0.5)
         ->click('Presence: 0')    // 0
         ->wait(0.5)
         ->click('Knowledge: -1')  // -1 (less academic focus)
         ->waitForText('Trait assignment complete!', 3);
    
    // Navigate to character info section
    $page->click('Next')
         ->waitForText('Character Information', 5);
    
    // Step 6: Verify character stats are calculated correctly
    // Seraph base: 7 HP, 9 Evasion, 6 Stress
    // Human ancestry: +2 Stress
    $page->assertSee('Hit Points: 7')  // Seraph base
         ->assertSee('Evasion: 9')     // Seraph base (no agility bonus)
         ->assertSee('Stress: 8')      // 6 base + 2 human
         ->assertSee('Spellcast Trait: Strength (+2)');
    
    // Fill in character name
    $page->type('#character-name', 'Valerian Lightbringer')
         ->type('#character-pronouns', 'he/him')
         ->waitForText('Character details saved', 3);
    
    // Navigate to equipment selection
    $page->click('Next')
         ->waitForText('Choose Your Equipment', 5);
    
    // Step 7: Select appropriate equipment for divine wielder
    $page->click('Blessed Sword')     // Divine warrior weapon
         ->wait(0.5)
         ->click('Chain Mail')        // Good protection
         ->wait(0.5)
         ->click('Holy Symbol')       // Divine focus
         ->waitForText('Equipment selection complete!', 3);
    
    // Navigate to background questions
    $page->click('Next')
         ->waitForText('Background Questions', 5);
    
    // Step 8: Answer seraph-specific background questions
    $page->type('#background-0', 'I was chosen by a dying angel to carry their blessed weapon and continue their mission of protecting the innocent.')
         ->wait(0.5)
         ->type('#background-1', 'A cult of shadow worshippers has corrupted a sacred temple, and I must cleanse it before they complete their dark ritual.')
         ->wait(0.5)
         ->type('#background-2', 'I fear that my mortal body isn\'t worthy of the divine power flowing through me, and that I\'ll fail in my sacred duty.')
         ->waitForText('Background complete!', 3);
    
    // Navigate to experiences
    $page->click('Next')
         ->waitForText('Choose Your Experiences', 5);
    
    // Step 9: Create two experiences
    $page->type('#experience-0', 'Divine Meditation')
         ->wait(0.5)
         ->type('#experience-1', 'Sacred Combat')
         ->waitForText('Experiences complete!', 3);
    
    // Navigate to domain cards
    $page->click('Next')
         ->waitForText('Choose Domain Cards', 5);
    
    // Step 10: Select domain cards from Splendor and Valor domains
    $page->assertSee('Available Domains: Splendor, Valor')
         ->click('Healing Light')     // Level 1 Splendor card
         ->wait(0.5)
         ->click('Rally')             // Level 1 Valor card
         ->waitForText('Domain cards selected!', 3)
         ->assertSee('2/2 cards selected');
    
    // Navigate to connections
    $page->click('Next')
         ->waitForText('Character Connections', 5);
    
    // Step 11: Answer connection questions
    $page->type('#connection-0', 'I used my divine power to save your life when you were dying, creating an unbreakable bond between our souls.')
         ->wait(0.5)
         ->type('#connection-1', 'You gave me a simple prayer book that belonged to your grandmother, and I treasure it more than any holy relic.')
         ->wait(0.5)
         ->type('#connection-2', 'You know that my divine powers sometimes fail me when I doubt myself, but you\'ve never spoken of this weakness.')
         ->waitForText('Connections complete!', 3);
    
    // Navigate to final character sheet
    $page->click('Finish Character')
         ->waitForText('Valerian Lightbringer', 5);
    
    // Final validation: Verify complete character sheet
    $page->assertSee('Valerian Lightbringer')
         ->assertSee('Seraph')
         ->assertSee('Divine Wielder')
         ->assertSee('Human')
         ->assertSee('Orderborne')
         ->assertSee('Hit Points: 7')
         ->assertSee('Evasion: 9')
         ->assertSee('Stress: 8')
         ->assertSee('Agility: 0')
         ->assertSee('Strength: +2')
         ->assertSee('Finesse: +1')
         ->assertSee('Instinct: +1')
         ->assertSee('Presence: 0')
         ->assertSee('Knowledge: -1')
         ->assertSee('Prayer Dice')
         ->assertSee('Spirit Weapon')
         ->assertSee('Sparing Touch')
         ->assertSee('Devout')
         ->assertSee('Sacred Resonance')
         ->assertSee('Healing Light')
         ->assertSee('Rally')
         ->assertSee('Blessed Sword')
         ->assertSee('Chain Mail')
         ->assertSee('Divine Meditation')
         ->assertSee('Sacred Combat');
    
    // Verify navigation to character viewer works
    $page->click('View Character')
         ->waitForText('Valerian Lightbringer', 5)
         ->assertSee('Seraph • Divine Wielder')
         ->assertSee('Human Orderborne');
    
    // Extract character key from URL to verify database persistence
    $currentUrl = $page->driver->getCurrentURL();
    preg_match('/character-viewer\/([A-Z0-9]{8,})/', $currentUrl, $matches);
    expect($matches)->toHaveCount(2);
    
    $characterKey = $matches[1];
    
    // Verify character was saved correctly in database
    $character = Character::where('character_key', $characterKey)->first();
    expect($character)->not()->toBeNull()
        ->class->toBe('seraph')
        ->subclass->toBe('divine wielder')
        ->ancestry->toBe('human')
        ->community->toBe('orderborne')
        ->name->toBe('Valerian Lightbringer')
        ->pronouns->toBe('he/him');
    
    // Verify traits were saved with correct distribution
    $traits = $character->traits()->pluck('trait_value', 'trait_name')->toArray();
    expect($traits)->toBe([
        'agility' => 0,
        'strength' => 2,
        'finesse' => 1,
        'instinct' => 1,
        'presence' => 0,
        'knowledge' => -1,
    ]);
    
    // Verify character stats calculation
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(7);  // Seraph base
    expect($stats->evasion)->toBe(9);     // Seraph base (no agility bonus)
    expect($stats->stress)->toBe(8);      // 6 base + 2 human
    
    // Verify spellcast trait is correct
    expect($character->getSpellcastTrait())->toBe('strength');
    expect($character->getSpellcastTraitValue())->toBe(2);
    
    // Verify domain cards were saved
    $domainCards = $character->domainCards()->pluck('card_name')->toArray();
    expect($domainCards)->toContain('Healing Light')
                       ->toContain('Rally');
    
    // Verify experiences were saved
    $experiences = $character->experiences()->pluck('experience_name')->toArray();
    expect($experiences)->toContain('Divine Meditation')
                       ->toContain('Sacred Combat');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('seraph divine wielder prayer dice mechanics validation', function () {
    // Test that Prayer Dice mechanics are properly explained
    // and session-based resource management is clear
    
    $page = visit('/character-builder');
    
    // Navigate through class selection
    $page->waitForText('Seraph', 10)
         ->click('Seraph');
    
    // Verify Prayer Dice feature is shown
    $page->assertSee('Prayer Dice')
         ->assertSee('beginning of each session')
         ->assertSee('roll a number of d4s')
         ->assertSee('equal to your subclass\'s Spellcast trait')
         ->assertSee('place them on your character sheet')
         ->assertSee('spend any number of Prayer Dice')
         ->assertSee('aid yourself or an ally within Far range')
         ->assertSee('reduce incoming damage')
         ->assertSee('add to a roll\'s result after the roll is made')
         ->assertSee('gain Hope equal to the result')
         ->assertSee('end of each session')
         ->assertSee('clear all unspent Prayer Dice');
    
    // Continue to subclass
    $page->click('Next')
         ->waitForText('Divine Wielder', 5)
         ->click('Divine Wielder');
    
    // Verify Divine Wielder enhances Prayer Dice through Devout
    $page->assertSee('Devout')
         ->assertSee('When you roll your Prayer Dice')
         ->assertSee('roll an additional die and discard the lowest result');
    
    // This improves Prayer Dice reliability and average values
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('seraph divine wielder spirit weapon mechanics validation', function () {
    // Test that Spirit Weapon mechanics are properly displayed
    // and weapon throwing/returning mechanics are explained
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Seraph', 10)
         ->click('Seraph')
         ->click('Next')
         ->waitForText('Divine Wielder', 5)
         ->click('Divine Wielder');
    
    // Verify Spirit Weapon mechanics
    $page->assertSee('Spirit Weapon')
         ->assertSee('equipped weapon with a range of Melee or Very Close')
         ->assertSee('fly from your hand to attack an adversary')
         ->assertSee('within Close range')
         ->assertSee('then return to you')
         ->assertSee('mark a Stress to target an additional adversary')
         ->assertSee('within range with the same attack roll');
    
    // Verify healing component
    $page->assertSee('Sparing Touch')
         ->assertSee('once per long rest')
         ->assertSee('touch a creature')
         ->assertSee('clear 2 Hit Points or 2 Stress from them');
    
    // Verify progression enhancements
    $page->assertSee('Devout')
         ->assertSee('twice instead of once per long rest');
    
    $page->assertSee('Sacred Resonance')
         ->assertSee('roll damage for your "Spirit Weapon" feature')
         ->assertSee('if any of the die results match')
         ->assertSee('double the value of each matching die')
         ->assertSee('two 5s, they count as two 10s');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('seraph divine wielder life support integration', function () {
    // Test that Divine Wielder healing features integrate with Seraph's Life Support
    
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
         ->waitForText('Divine Wielder', 5)
         ->click('Divine Wielder');
    
    // Verify Divine Wielder healing features complement Life Support
    $page->assertSee('Sparing Touch')  // Rest-based healing
         ->assertSee('clear 2 Hit Points or 2 Stress') // More efficient than Life Support
         ->assertSee('Devout')          // Doubles Sparing Touch usage
         ->assertSee('twice instead of once per long rest');
    
    // The synergy: Life Support for emergency Hope-based healing,
    // Sparing Touch for efficient rest-based healing
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('seraph divine wielder domain access verification', function () {
    // Test that Seraph class provides correct domain access (Splendor + Valor)
    // and Divine Wielder doesn't modify domain access
    
    $page = visit('/character-builder');
    
    // Navigate to domain cards step
    $page->waitForText('Seraph', 10)
         ->click('Seraph')
         ->click('Next')
         ->waitForText('Divine Wielder', 5)
         ->click('Divine Wielder');
    
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
    $page->assertPresent('[data-testid="domain-card-healing-light"]')
         ->assertPresent('[data-testid="domain-card-rally"]');
    
    // Verify domain cards from other domains are NOT available
    $page->assertDontSee('Arcana Domain')
         ->assertDontSee('Midnight Domain')
         ->assertDontSee('Grace Domain')
         ->assertDontSee('Bone Domain');
    
    // Verify can select exactly 2 cards
    $page->click('Healing Light')
         ->wait(0.5)
         ->assertSee('1/2 cards selected')
         ->click('Rally')
         ->wait(0.5)
         ->assertSee('2/2 cards selected');
    
    // Verify cannot select a third card (buttons should be disabled)
    $remainingCards = $page->elements('[data-testid*="domain-card-"]:not([data-selected])');
    foreach ($remainingCards as $card) {
        expect($card->getAttribute('disabled'))->toBe('true');
    }
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('seraph divine wielder spellcast trait validation', function () {
    // Test that Divine Wielder correctly assigns Strength as spellcast trait
    // (unusual for a divine class, emphasizing physical divine warrior aspect)
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Seraph', 10)
         ->click('Seraph')
         ->click('Next')
         ->waitForText('Divine Wielder', 5)
         ->click('Divine Wielder');
    
    // Verify spellcast trait is displayed
    $page->assertSee('Spellcast Trait: Strength')
         ->assertSee('Your divine power uses Strength');
    
    // Navigate to trait assignment to verify integration
    for ($i = 0; $i < 3; $i++) { // Skip ancestry, community
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Assign Your Traits', 5);
    
    // Verify Strength is highlighted as spellcast trait
    $page->assertSee('Strength (Spellcast)')
         ->assertSee('This is your primary divine power trait');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
