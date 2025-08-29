<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive Pest 4 browser test for Wizard + School of War character creation workflow
 * 
 * This test validates the complete character creation process through UI interactions:
 * - Step 1: Class selection (Wizard)
 * - Step 2: Subclass selection (School of War)
 * - Step 3: Ancestry selection  
 * - Step 4: Community selection
 * - Step 5: Trait assignment (validates exact -1,0,0,+1,+1,+2 distribution)
 * - Step 6: Character stats verification
 * - Step 7: Equipment selection
 * - Step 8: Background questions
 * - Step 9: Experiences
 * - Step 10: Domain cards (Codex + Midnight access)
 * - Step 11: Connections
 * 
 * Wizard + School of War specific validations:
 * - Wizard base stats (Hit Points: 5, Evasion: 9, Stress: 6)
 * - School of War uses Knowledge as spellcast trait
 * - Battlemage: permanent +1 Hit Point bonus
 * - Face Your Fear: Fear-based damage scaling (1d10/2d10/3d10)
 * - Conjure Shield: Hope-based Evasion enhancement
 * - Thrive in Chaos: stress-based additional Hit Point marking
 * - Prepared Mind and Arcane Recovery integration
 * - Study and Research class features
 */

test('wizard school of war complete character creation workflow', function () {
    // Start at character builder main page
    $page = visit('/character-builder');
    
    // Verify initial page load
    $page->assertSee('Character Builder')
         ->waitForText('Wizard', 10);
    
    // Step 1: Select Wizard class
    $page->click('Wizard')
         ->waitForText('Wizard', 5)
         ->assertSee('Codex')
         ->assertSee('Midnight')
         ->assertSee('Starting Evasion: 9')
         ->assertSee('Starting Hit Points: 5')
         ->assertSee('Prepared Mind')
         ->assertSee('Arcane Recovery')
         ->assertSee('Study')
         ->assertSee('scholarly magic');
    
    // Navigate to subclass selection
    $page->click('Next')
         ->waitForText('School of War', 5);
    
    // Step 2: Select School of War subclass
    $page->click('School of War')
         ->waitForText('School of War', 5)
         ->assertSee('Battlemage')
         ->assertSee('additional Hit Point slot')
         ->assertSee('Face Your Fear')
         ->assertSee('succeed with Fear on an attack roll')
         ->assertSee('1d10 magic damage')
         ->assertSee('Conjure Shield')
         ->assertSee('maintain a protective barrier of magic')
         ->assertSee('at least 2 Hope')
         ->assertSee('add your Proficiency to your Evasion')
         ->assertSee('Fueled by Fear')
         ->assertSee('increases to 2d10')
         ->assertSee('Thrive in Chaos')
         ->assertSee('mark a Stress after rolling damage')
         ->assertSee('additional Hit Point')
         ->assertSee('Have No Fear')
         ->assertSee('increases to 3d10')
         ->assertSee('Spellcast Trait: Knowledge');
    
    // Navigate to ancestry selection
    $page->click('Next')
         ->waitForText('Choose Your Ancestry', 5);
    
    // Step 3: Select Elf ancestry (keen intellect for wizard)
    $page->click('Elf')
         ->waitForText('Elf', 5)
         ->assertSee('Graceful beings')
         ->assertSee('keen senses');
    
    // Navigate to community selection
    $page->click('Next')
         ->waitForText('Choose Your Community', 5);
    
    // Step 4: Select Loreborne community (academic, perfect for wizard)
    $page->click('Loreborne')
         ->waitForText('Loreborne', 5)
         ->assertSee('Academic')
         ->assertSee('knowledge-seeking');
    
    // Navigate to trait assignment
    $page->click('Next')
         ->waitForText('Assign Your Traits', 5);
    
    // Step 5: Assign traits with exact DaggerHeart distribution (-1,0,0,+1,+1,+2)
    // Focus on Knowledge (spellcast trait) and combat-wizard capabilities
    $page->click('Agility: +1')   // +1 (mobility in combat)
         ->wait(0.5)
         ->click('Strength: 0')   // 0
         ->wait(0.5)
         ->click('Finesse: +1')   // +1 (weapon precision)
         ->wait(0.5)
         ->click('Instinct: 0')   // 0
         ->wait(0.5)
         ->click('Presence: -1')  // -1 (less social focus)
         ->wait(0.5)
         ->click('Knowledge: +2') // +2 (primary spellcast trait)
         ->waitForText('Trait assignment complete!', 3);
    
    // Navigate to character info section
    $page->click('Next')
         ->waitForText('Character Information', 5);
    
    // Step 6: Verify character stats are calculated correctly
    // Wizard base: 5 HP, 9 Evasion, 6 Stress
    // Battlemage: +1 Hit Point
    // Agility +1 affects evasion
    $page->assertSee('Hit Points: 6')  // 5 base + 1 Battlemage
         ->assertSee('Evasion: 10')    // 9 base + 1 agility
         ->assertSee('Stress: 6')      // Wizard base
         ->assertSee('Spellcast Trait: Knowledge (+2)');
    
    // Fill in character name
    $page->type('#character-name', 'Arcanum Spellsword')
         ->type('#character-pronouns', 'they/them')
         ->waitForText('Character details saved', 3);
    
    // Navigate to equipment selection
    $page->click('Next')
         ->waitForText('Choose Your Equipment', 5);
    
    // Step 7: Select appropriate equipment for combat wizard
    $page->click('Staff and Blade')   // Combat wizard weapons
         ->wait(0.5)
         ->click('Battle Robes')      // Enhanced protection
         ->wait(0.5)
         ->click('Spell Components')  // Magical focus
         ->waitForText('Equipment selection complete!', 3);
    
    // Navigate to background questions
    $page->click('Next')
         ->waitForText('Background Questions', 5);
    
    // Step 8: Answer wizard-specific background questions
    $page->type('#background-0', 'I was expelled from the Grand Academy for researching forbidden battle magic that the professors deemed too dangerous.')
         ->wait(0.5)
         ->type('#background-1', 'My mentor was a war wizard who taught me that knowledge without the strength to defend it is worthless.')
         ->wait(0.5)
         ->type('#background-2', 'I fear that my pursuit of combat magic is corrupting my pure love of academic learning and research.')
         ->waitForText('Background complete!', 3);
    
    // Navigate to experiences
    $page->click('Next')
         ->waitForText('Choose Your Experiences', 5);
    
    // Step 9: Create two experiences
    $page->type('#experience-0', 'Battle Tactics')
         ->wait(0.5)
         ->type('#experience-1', 'Arcane Theory')
         ->waitForText('Experiences complete!', 3);
    
    // Navigate to domain cards
    $page->click('Next')
         ->waitForText('Choose Domain Cards', 5);
    
    // Step 10: Select domain cards from Codex and Midnight domains
    $page->assertSee('Available Domains: Codex, Midnight')
         ->click('Identify')          // Level 1 Codex card
         ->wait(0.5)
         ->click('Shadow Bolt')       // Level 1 Midnight card
         ->waitForText('Domain cards selected!', 3)
         ->assertSee('2/2 cards selected');
    
    // Navigate to connections
    $page->click('Next')
         ->waitForText('Character Connections', 5);
    
    // Step 11: Answer connection questions
    $page->type('#connection-0', 'You saved me from assassins sent by the Academy, proving that my research into war magic was worth the exile.')
         ->wait(0.5)
         ->type('#connection-1', 'I accidentally used you as a test subject for a new spell, and now I owe you a debt that can never be repaid.')
         ->wait(0.5)
         ->type('#connection-2', 'We both know that my spellbook contains forbidden knowledge that could reshape the world if it fell into the wrong hands.')
         ->waitForText('Connections complete!', 3);
    
    // Navigate to final character sheet
    $page->click('Finish Character')
         ->waitForText('Arcanum Spellsword', 5);
    
    // Final validation: Verify complete character sheet
    $page->assertSee('Arcanum Spellsword')
         ->assertSee('Wizard')
         ->assertSee('School of War')
         ->assertSee('Elf')
         ->assertSee('Loreborne')
         ->assertSee('Hit Points: 6')
         ->assertSee('Evasion: 10')
         ->assertSee('Stress: 6')
         ->assertSee('Agility: +1')
         ->assertSee('Strength: 0')
         ->assertSee('Finesse: +1')
         ->assertSee('Instinct: 0')
         ->assertSee('Presence: -1')
         ->assertSee('Knowledge: +2')
         ->assertSee('Battlemage')
         ->assertSee('Face Your Fear')
         ->assertSee('Conjure Shield')
         ->assertSee('Thrive in Chaos')
         ->assertSee('Identify')
         ->assertSee('Shadow Bolt')
         ->assertSee('Staff and Blade')
         ->assertSee('Battle Robes')
         ->assertSee('Battle Tactics')
         ->assertSee('Arcane Theory');
    
    // Verify navigation to character viewer works
    $page->click('View Character')
         ->waitForText('Arcanum Spellsword', 5)
         ->assertSee('Wizard • School of War')
         ->assertSee('Elf Loreborne');
    
    // Extract character key from URL to verify database persistence
    $currentUrl = $page->driver->getCurrentURL();
    preg_match('/character-viewer\/([A-Z0-9]{8,})/', $currentUrl, $matches);
    expect($matches)->toHaveCount(2);
    
    $characterKey = $matches[1];
    
    // Verify character was saved correctly in database
    $character = Character::where('character_key', $characterKey)->first();
    expect($character)->not()->toBeNull()
        ->class->toBe('wizard')
        ->subclass->toBe('school of war')
        ->ancestry->toBe('elf')
        ->community->toBe('loreborne')
        ->name->toBe('Arcanum Spellsword')
        ->pronouns->toBe('they/them');
    
    // Verify traits were saved with correct distribution
    $traits = $character->traits()->pluck('trait_value', 'trait_name')->toArray();
    expect($traits)->toBe([
        'agility' => 1,
        'strength' => 0,
        'finesse' => 1,
        'instinct' => 0,
        'presence' => -1,
        'knowledge' => 2,
    ]);
    
    // Verify character stats calculation
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(6);  // 5 base + 1 Battlemage
    expect($stats->evasion)->toBe(10);    // 9 base + 1 agility
    expect($stats->stress)->toBe(6);      // Wizard base
    
    // Verify subclass bonuses
    expect($character->getSubclassHitPointBonus())->toBe(1); // Battlemage permanent bonus
    
    // Verify spellcast trait is correct
    expect($character->getSpellcastTrait())->toBe('knowledge');
    expect($character->getSpellcastTraitValue())->toBe(2);
    
    // Verify domain cards were saved
    $domainCards = $character->domainCards()->pluck('card_name')->toArray();
    expect($domainCards)->toContain('Identify')
                       ->toContain('Shadow Bolt');
    
    // Verify experiences were saved
    $experiences = $character->experiences()->pluck('experience_name')->toArray();
    expect($experiences)->toContain('Battle Tactics')
                       ->toContain('Arcane Theory');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('wizard school of war fear damage progression validation', function () {
    // Test that School of War's Fear-based damage progression is properly displayed
    // Foundation: 1d10, Specialization: 2d10, Mastery: 3d10
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Wizard', 10)
         ->click('Wizard')
         ->click('Next')
         ->waitForText('School of War', 5)
         ->click('School of War');
    
    // Verify Fear damage progression
    $page->assertSee('Face Your Fear')
         ->assertSee('succeed with Fear on an attack roll')
         ->assertSee('deal an extra 1d10 magic damage');
    
    $page->assertSee('Fueled by Fear')
         ->assertSee('The extra magic damage from your "Face Your Fear" feature')
         ->assertSee('increases to 2d10');
    
    $page->assertSee('Have No Fear')
         ->assertSee('The extra magic damage from your "Face Your Fear" feature')
         ->assertSee('increases to 3d10');
    
    // This creates a powerful escalation:
    // Level 1-2: +1d10 magic damage on Fear successes
    // Level 3-4: +2d10 magic damage on Fear successes  
    // Level 5+: +3d10 magic damage on Fear successes
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('wizard school of war battlemage survivability validation', function () {
    // Test that School of War's survivability features are properly displayed
    // Battlemage (+1 HP) and Conjure Shield (Proficiency to Evasion)
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Wizard', 10)
         ->click('Wizard')
         ->click('Next')
         ->waitForText('School of War', 5)
         ->click('School of War');
    
    // Verify survivability features
    $page->assertSee('Battlemage')
         ->assertSee('additional Hit Point slot')
         ->assertSee('unconquerable force on the battlefield');
    
    $page->assertSee('Conjure Shield')
         ->assertSee('maintain a protective barrier of magic')
         ->assertSee('While you have at least 2 Hope')
         ->assertSee('add your Proficiency to your Evasion');
    
    $page->assertSee('Thrive in Chaos')
         ->assertSee('succeed on an attack')
         ->assertSee('mark a Stress after rolling damage')
         ->assertSee('force the target to mark an additional Hit Point');
    
    // This makes the wizard much more survivable:
    // - +1 Hit Point for durability
    // - Proficiency bonus to Evasion when at 2+ Hope
    // - Ability to force extra damage through stress
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('wizard school of war prepared mind integration', function () {
    // Test that School of War features integrate with Wizard's academic abilities
    
    $page = visit('/character-builder');
    
    // Navigate through class selection
    $page->waitForText('Wizard', 10)
         ->click('Wizard');
    
    // Verify Wizard's academic features are shown
    $page->assertSee('Prepared Mind')
         ->assertSee('domain cards to vault')
         ->assertSee('hope equal to the highest level')
         ->assertSee('Arcane Recovery')
         ->assertSee('recall a domain card from your vault')
         ->assertSee('reduce its Recall Cost by 1')
         ->assertSee('Study')
         ->assertSee('Research')
         ->assertSee('ask the GM a question');
    
    // Continue to subclass
    $page->click('Next')
         ->waitForText('School of War', 5)
         ->click('School of War');
    
    // Verify School of War combines academic knowledge with combat prowess
    $page->assertSee('Battlemage')    // Physical combat enhancement
         ->assertSee('Face Your Fear') // Fear-based magic damage
         ->assertSee('Conjure Shield'); // Active magical defense
    
    // The synergy: Academic preparation enhances magical capabilities,
    // School of War adds combat survivability and damage scaling
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('wizard school of war domain access verification', function () {
    // Test that Wizard class provides correct domain access (Codex + Midnight)
    // and School of War doesn't modify domain access
    
    $page = visit('/character-builder');
    
    // Navigate to domain cards step
    $page->waitForText('Wizard', 10)
         ->click('Wizard')
         ->click('Next')
         ->waitForText('School of War', 5)
         ->click('School of War');
    
    // Skip to domain cards section
    for ($i = 0; $i < 7; $i++) { // Skip ancestry, community, traits, info, equipment, background, experiences
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Choose Domain Cards', 5);
    
    // Verify correct domains are available
    $page->assertSee('Available Domains: Codex, Midnight')
         ->assertSee('Codex Domain')
         ->assertSee('Midnight Domain');
    
    // Verify level 1 cards from both domains are available
    $page->assertPresent('[data-testid="domain-card-identify"]')
         ->assertPresent('[data-testid="domain-card-shadow-bolt"]');
    
    // Verify domain cards from other domains are NOT available
    $page->assertDontSee('Arcana Domain')
         ->assertDontSee('Splendor Domain')
         ->assertDontSee('Grace Domain')
         ->assertDontSee('Valor Domain');
    
    // Verify can select exactly 2 cards
    $page->click('Identify')
         ->wait(0.5)
         ->assertSee('1/2 cards selected')
         ->click('Shadow Bolt')
         ->wait(0.5)
         ->assertSee('2/2 cards selected');
    
    // Verify cannot select a third card (buttons should be disabled)
    $remainingCards = $page->elements('[data-testid*="domain-card-"]:not([data-selected])');
    foreach ($remainingCards as $card) {
        expect($card->getAttribute('disabled'))->toBe('true');
    }
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('wizard school of war hit point bonus validation', function () {
    // Test that School of War's Battlemage provides permanent +1 Hit Point
    // This is critical for wizard survivability in combat
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Wizard', 10)
         ->click('Wizard')
         ->click('Next')
         ->waitForText('School of War', 5)
         ->click('School of War');
    
    // Verify Battlemage permanent bonus is clearly stated
    $page->assertSee('Battlemage')
         ->assertSee('additional Hit Point slot')
         ->assertSee('permanent bonus');
    
    // Navigate to stats to verify this is applied
    for ($i = 0; $i < 5; $i++) { // Skip to character info
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Character Information', 5);
    
    // Should see base HP + Battlemage bonus
    $page->assertSee('Hit Points: 6')  // 5 base + 1 Battlemage
         ->assertSee('School of War Hit Point Bonus: +1');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('wizard school of war spellcast trait validation', function () {
    // Test that School of War correctly assigns Knowledge as spellcast trait
    // (same as other wizard subclasses)
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Wizard', 10)
         ->click('Wizard')
         ->click('Next')
         ->waitForText('School of War', 5)
         ->click('School of War');
    
    // Verify spellcast trait is displayed
    $page->assertSee('Spellcast Trait: Knowledge')
         ->assertSee('Your trained magic uses Knowledge');
    
    // Navigate to trait assignment to verify integration
    for ($i = 0; $i < 3; $i++) { // Skip ancestry, community
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Assign Your Traits', 5);
    
    // Verify Knowledge is highlighted as spellcast trait
    $page->assertSee('Knowledge (Spellcast)')
         ->assertSee('This is your primary magical study trait');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
