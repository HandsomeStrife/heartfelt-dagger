<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive Pest 4 browser test for Bard + Troubadour character creation workflow
 * 
 * This test validates the complete character creation process through UI interactions:
 * - Step 1: Class selection (Bard)
 * - Step 2: Subclass selection (Troubadour)
 * - Step 3: Ancestry selection  
 * - Step 4: Community selection
 * - Step 5: Trait assignment (validates exact -1,0,0,+1,+1,+2 distribution)
 * - Step 6: Character stats verification
 * - Step 7: Equipment selection
 * - Step 8: Background questions
 * - Step 9: Experiences
 * - Step 10: Domain cards (Grace + Codex access)
 * - Step 11: Connections
 * 
 * Bard + Troubadour specific validations:
 * - Bard base stats (Hit Points: 5, Evasion: 10, Stress: 6)
 * - Troubadour uses Presence as spellcast trait
 * - Gifted Performer: 3 song types (Relaxing, Epic, Heartbreaking)
 * - Song usage limitations (once each per long rest)
 * - Maestro: Rally Die enhancements for allies
 * - Virtuoso: Doubled song usage (twice per long rest)
 * - Rally class feature integration with Troubadour bonuses
 */

test('bard troubadour complete character creation workflow', function () {
    // Start at character builder main page
    $page = visit('/character-builder');
    
    // Verify initial page load
    $page->assertSee('Character Builder')
         ->waitForText('Bard', 10);
    
    // Step 1: Select Bard class
    $page->click('Bard')
         ->waitForText('Bard', 5)
         ->assertSee('Grace')
         ->assertSee('Codex')
         ->assertSee('Starting Evasion: 10')
         ->assertSee('Starting Hit Points: 5')
         ->assertSee('Rally')
         ->assertSee('inspire and aid allies');
    
    // Navigate to subclass selection
    $page->click('Next')
         ->waitForText('Troubadour', 5);
    
    // Step 2: Select Troubadour subclass
    $page->click('Troubadour')
         ->waitForText('Troubadour', 5)
         ->assertSee('Gifted Performer')
         ->assertSee('three different types of songs')
         ->assertSee('Relaxing Song')
         ->assertSee('clear a Hit Point')
         ->assertSee('Epic Song')
         ->assertSee('temporarily Vulnerable')
         ->assertSee('Heartbreaking Song')
         ->assertSee('gain a Hope')
         ->assertSee('once each per long rest')
         ->assertSee('Maestro')
         ->assertSee('Rally Die')
         ->assertSee('Hope or clear a Stress')
         ->assertSee('Virtuoso')
         ->assertSee('twice per long rest')
         ->assertSee('Spellcast Trait: Presence');
    
    // Navigate to ancestry selection
    $page->click('Next')
         ->waitForText('Choose Your Ancestry', 5);
    
    // Step 3: Select Elf ancestry (thematic for musical bard)
    $page->click('Elf')
         ->waitForText('Elf', 5)
         ->assertSee('Graceful beings')
         ->assertSee('keen senses');
    
    // Navigate to community selection
    $page->click('Next')
         ->waitForText('Choose Your Community', 5);
    
    // Step 4: Select Highborne community (aristocratic, fits performance theme)
    $page->click('Highborne')
         ->waitForText('Highborne', 5)
         ->assertSee('Wealthy')
         ->assertSee('court-trained');
    
    // Navigate to trait assignment
    $page->click('Next')
         ->waitForText('Assign Your Traits', 5);
    
    // Step 5: Assign traits with exact DaggerHeart distribution (-1,0,0,+1,+1,+2)
    // Focus on Presence (spellcast trait) and social capabilities
    $page->click('Agility: +1')   // +1 (performance mobility)
         ->wait(0.5)
         ->click('Strength: -1')  // -1 (not physical focused)
         ->wait(0.5)
         ->click('Finesse: +1')   // +1 (musical dexterity)
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
    // Bard base: 5 HP, 10 Evasion, 6 Stress
    // Agility +1 affects evasion
    $page->assertSee('Hit Points: 5')  // Bard base
         ->assertSee('Evasion: 11')    // 10 base + 1 agility
         ->assertSee('Stress: 6')      // Bard base
         ->assertSee('Spellcast Trait: Presence (+2)');
    
    // Fill in character name
    $page->type('#character-name', 'Melody Songweaver')
         ->type('#character-pronouns', 'she/her')
         ->waitForText('Character details saved', 3);
    
    // Navigate to equipment selection
    $page->click('Next')
         ->waitForText('Choose Your Equipment', 5);
    
    // Step 7: Select appropriate equipment for bard
    $page->click('Lute')             // Musical instrument
         ->wait(0.5)
         ->click('Leather Armor')     // Light armor for mobility
         ->wait(0.5)
         ->click('Performance Kit')   // Bard-specific equipment
         ->waitForText('Equipment selection complete!', 3);
    
    // Navigate to background questions
    $page->click('Next')
         ->waitForText('Background Questions', 5);
    
    // Step 8: Answer bard-specific background questions
    $page->type('#background-0', 'My mentor was a legendary court bard who taught me that music has the power to heal hearts and change minds.')
         ->wait(0.5)
         ->type('#background-1', 'I once loved a fellow musician who stole my original compositions and claimed them as their own, breaking my trust.')
         ->wait(0.5)
         ->type('#background-2', 'I fear that my music isn\'t powerful enough to truly help people in their darkest moments of need.')
         ->waitForText('Background complete!', 3);
    
    // Navigate to experiences
    $page->click('Next')
         ->waitForText('Choose Your Experiences', 5);
    
    // Step 9: Create two experiences
    $page->type('#experience-0', 'Court Performance')
         ->wait(0.5)
         ->type('#experience-1', 'Storytelling')
         ->waitForText('Experiences complete!', 3);
    
    // Navigate to domain cards
    $page->click('Next')
         ->waitForText('Choose Domain Cards', 5);
    
    // Step 10: Select domain cards from Grace and Codex domains
    $page->assertSee('Available Domains: Grace, Codex')
         ->click('Charm Person')      // Level 1 Grace card
         ->wait(0.5)
         ->click('Identify')          // Level 1 Codex card
         ->waitForText('Domain cards selected!', 3)
         ->assertSee('2/2 cards selected');
    
    // Navigate to connections
    $page->click('Next')
         ->waitForText('Character Connections', 5);
    
    // Step 11: Answer connection questions
    $page->type('#connection-0', 'You believed in my musical abilities when everyone else doubted me, giving me the confidence to perform before crowds.')
         ->wait(0.5)
         ->type('#connection-1', 'I accidentally revealed one of your secrets in a song I performed, and now I\'m more careful with the stories I tell.')
         ->wait(0.5)
         ->type('#connection-2', 'We both know that my lullabies contain ancient magic that certain scholars would kill to understand.')
         ->waitForText('Connections complete!', 3);
    
    // Navigate to final character sheet
    $page->click('Finish Character')
         ->waitForText('Melody Songweaver', 5);
    
    // Final validation: Verify complete character sheet
    $page->assertSee('Melody Songweaver')
         ->assertSee('Bard')
         ->assertSee('Troubadour')
         ->assertSee('Elf')
         ->assertSee('Highborne')
         ->assertSee('Hit Points: 5')
         ->assertSee('Evasion: 11')
         ->assertSee('Stress: 6')
         ->assertSee('Agility: +1')
         ->assertSee('Strength: -1')
         ->assertSee('Finesse: +1')
         ->assertSee('Instinct: 0')
         ->assertSee('Presence: +2')
         ->assertSee('Knowledge: 0')
         ->assertSee('Gifted Performer')
         ->assertSee('Relaxing Song')
         ->assertSee('Epic Song')
         ->assertSee('Heartbreaking Song')
         ->assertSee('Maestro')
         ->assertSee('Virtuoso')
         ->assertSee('Charm Person')
         ->assertSee('Identify')
         ->assertSee('Lute')
         ->assertSee('Leather Armor')
         ->assertSee('Court Performance')
         ->assertSee('Storytelling');
    
    // Verify navigation to character viewer works
    $page->click('View Character')
         ->waitForText('Melody Songweaver', 5)
         ->assertSee('Bard • Troubadour')
         ->assertSee('Elf Highborne');
    
    // Extract character key from URL to verify database persistence
    $currentUrl = $page->driver->getCurrentURL();
    preg_match('/character-viewer\/([A-Z0-9]{8,})/', $currentUrl, $matches);
    expect($matches)->toHaveCount(2);
    
    $characterKey = $matches[1];
    
    // Verify character was saved correctly in database
    $character = Character::where('character_key', $characterKey)->first();
    expect($character)->not()->toBeNull()
        ->class->toBe('bard')
        ->subclass->toBe('troubadour')
        ->ancestry->toBe('elf')
        ->community->toBe('highborne')
        ->name->toBe('Melody Songweaver')
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
    expect($stats->hit_points)->toBe(5);  // Bard base
    expect($stats->evasion)->toBe(11);    // 10 base + 1 agility
    expect($stats->stress)->toBe(6);      // Bard base
    
    // Verify spellcast trait is correct
    expect($character->getSpellcastTrait())->toBe('presence');
    expect($character->getSpellcastTraitValue())->toBe(2);
    
    // Verify domain cards were saved
    $domainCards = $character->domainCards()->pluck('card_name')->toArray();
    expect($domainCards)->toContain('Charm Person')
                       ->toContain('Identify');
    
    // Verify experiences were saved
    $experiences = $character->experiences()->pluck('experience_name')->toArray();
    expect($experiences)->toContain('Court Performance')
                       ->toContain('Storytelling');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('bard troubadour song mechanics validation', function () {
    // Test that Troubadour song mechanics are properly displayed
    // and usage limitations are explained
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Bard', 10)
         ->click('Bard')
         ->click('Next')
         ->waitForText('Troubadour', 5)
         ->click('Troubadour');
    
    // Verify all three song types are described
    $page->assertSee('Gifted Performer')
         ->assertSee('three different types of songs');
    
    // Test Relaxing Song details
    $page->assertSee('Relaxing Song')
         ->assertSee('You and all allies within Close range clear a Hit Point')
         ->assertSee('once each per long rest');
    
    // Test Epic Song details
    $page->assertSee('Epic Song')
         ->assertSee('Make a target within Close range temporarily Vulnerable')
         ->assertSee('once each per long rest');
    
    // Test Heartbreaking Song details
    $page->assertSee('Heartbreaking Song')
         ->assertSee('You and all allies within Close range gain a Hope')
         ->assertSee('once each per long rest');
    
    // Verify progression mechanics
    $page->assertSee('Maestro')
         ->assertSee('When you give a Rally Die to an ally')
         ->assertSee('they can gain a Hope or clear a Stress');
    
    $page->assertSee('Virtuoso')
         ->assertSee('perform each of your "Gifted Performer" feature\'s songs twice per long rest');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('bard troubadour rally integration validation', function () {
    // Test that Troubadour features integrate properly with Bard's Rally ability
    
    $page = visit('/character-builder');
    
    // Navigate through class selection
    $page->waitForText('Bard', 10)
         ->click('Bard');
    
    // Verify Bard Rally feature is shown
    $page->assertSee('Rally')
         ->assertSee('Rally Die')
         ->assertSee('help an ally');
    
    // Continue to subclass
    $page->click('Next')
         ->waitForText('Troubadour', 5)
         ->click('Troubadour');
    
    // Verify Troubadour enhances Rally through Maestro
    $page->assertSee('Maestro')
         ->assertSee('rallying songs steel the courage')
         ->assertSee('When you give a Rally Die to an ally')
         ->assertSee('they can gain a Hope or clear a Stress');
    
    // This creates powerful synergy:
    // - Base Rally: Give ally a Rally Die
    // - Maestro enhancement: Ally also gains Hope OR clears Stress
    // - Combined with songs for additional party support
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('bard troubadour domain access verification', function () {
    // Test that Bard class provides correct domain access (Grace + Codex)
    // and Troubadour doesn't modify domain access
    
    $page = visit('/character-builder');
    
    // Navigate to domain cards step
    $page->waitForText('Bard', 10)
         ->click('Bard')
         ->click('Next')
         ->waitForText('Troubadour', 5)
         ->click('Troubadour');
    
    // Skip to domain cards section
    for ($i = 0; $i < 7; $i++) { // Skip ancestry, community, traits, info, equipment, background, experiences
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Choose Domain Cards', 5);
    
    // Verify correct domains are available
    $page->assertSee('Available Domains: Grace, Codex')
         ->assertSee('Grace Domain')
         ->assertSee('Codex Domain');
    
    // Verify level 1 cards from both domains are available
    $page->assertPresent('[data-testid="domain-card-charm-person"]')
         ->assertPresent('[data-testid="domain-card-identify"]');
    
    // Verify domain cards from other domains are NOT available
    $page->assertDontSee('Arcana Domain')
         ->assertDontSee('Blade Domain')
         ->assertDontSee('Valor Domain')
         ->assertDontSee('Midnight Domain');
    
    // Verify can select exactly 2 cards
    $page->click('Charm Person')
         ->wait(0.5)
         ->assertSee('1/2 cards selected')
         ->click('Identify')
         ->wait(0.5)
         ->assertSee('2/2 cards selected');
    
    // Verify cannot select a third card (buttons should be disabled)
    $remainingCards = $page->elements('[data-testid*="domain-card-"]:not([data-selected])');
    foreach ($remainingCards as $card) {
        expect($card->getAttribute('disabled'))->toBe('true');
    }
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('bard troubadour spellcast trait validation', function () {
    // Test that Troubadour correctly assigns Presence as spellcast trait
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Bard', 10)
         ->click('Bard')
         ->click('Next')
         ->waitForText('Troubadour', 5)
         ->click('Troubadour');
    
    // Verify spellcast trait is displayed
    $page->assertSee('Spellcast Trait: Presence')
         ->assertSee('Your musical magic uses Presence');
    
    // Navigate to trait assignment to verify integration
    for ($i = 0; $i < 3; $i++) { // Skip ancestry, community
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Assign Your Traits', 5);
    
    // Verify Presence is highlighted as spellcast trait
    $page->assertSee('Presence (Spellcast)')
         ->assertSee('This is your primary musical trait');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
