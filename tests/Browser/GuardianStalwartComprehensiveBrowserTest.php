<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive Pest 4 browser test for Guardian + Stalwart character creation workflow
 * 
 * This test validates the complete character creation process through UI interactions:
 * - Step 1: Class selection (Guardian)
 * - Step 2: Subclass selection (Stalwart)
 * - Step 3: Ancestry selection  
 * - Step 4: Community selection
 * - Step 5: Trait assignment (validates exact -1,0,0,+1,+1,+2 distribution)
 * - Step 6: Character stats verification
 * - Step 7: Equipment selection
 * - Step 8: Background questions
 * - Step 9: Experiences
 * - Step 10: Domain cards (Valor + Blade access)
 * - Step 11: Connections
 * 
 * Guardian + Stalwart specific validations:
 * - Guardian base stats (Hit Points: 7, Evasion: 9, Stress: 6)
 * - Stalwart damage threshold bonuses (+1+2+3 = +6 total permanent bonus)
 * - Unwavering, Unrelenting, Undaunted features
 * - Iron Will armor slot mechanism
 * - Partners-in-Arms and Loyal Protector ally protection
 * - Unstoppable class feature integration
 */

test('guardian stalwart complete character creation workflow', function () {
    // Start at character builder main page
    $page = visit('/character-builder');
    
    // Verify initial page load
    $page->assertSee('Character Builder')
         ->waitForText('Guardian', 10);
    
    // Step 1: Select Guardian class
    $page->click('Guardian')
         ->waitForText('Guardian', 5)
         ->assertSee('Valor')
         ->assertSee('Blade')
         ->assertSee('Starting Evasion: 9')
         ->assertSee('Starting Hit Points: 7')
         ->assertSee('Guardians are protective warriors');
    
    // Navigate to subclass selection
    $page->click('Next')
         ->waitForText('Stalwart', 5);
    
    // Step 2: Select Stalwart subclass
    $page->click('Stalwart')
         ->waitForText('Stalwart', 5)
         ->assertSee('Unwavering')
         ->assertSee('+1 bonus to your damage thresholds')
         ->assertSee('Iron Will')
         ->assertSee('additional Armor Slot')
         ->assertSee('Unrelenting')
         ->assertSee('+2 bonus to your damage thresholds')
         ->assertSee('Undaunted')
         ->assertSee('+3 bonus to your damage thresholds')
         ->assertSee('Partners-in-Arms')
         ->assertSee('Loyal Protector');
    
    // Navigate to ancestry selection
    $page->click('Next')
         ->waitForText('Choose Your Ancestry', 5);
    
    // Step 3: Select Dwarf ancestry (thematic for defensive guardian)
    $page->click('Dwarf')
         ->waitForText('Dwarf', 5)
         ->assertSee('Hardy folk')
         ->assertSee('stone-skin');
    
    // Navigate to community selection
    $page->click('Next')
         ->waitForText('Choose Your Community', 5);
    
    // Step 4: Select Ridgeborne community (mountain-dwelling, fits dwarf guardian)
    $page->click('Ridgeborne')
         ->waitForText('Ridgeborne', 5)
         ->assertSee('Mountain-dwelling')
         ->assertSee('sturdy');
    
    // Navigate to trait assignment
    $page->click('Next')
         ->waitForText('Assign Your Traits', 5);
    
    // Step 5: Assign traits with exact DaggerHeart distribution (-1,0,0,+1,+1,+2)
    // Focus on defensive/protective traits for guardian
    $page->click('Agility: 0')   // 0
         ->wait(0.5)
         ->click('Strength: +2') // +2 (primary defensive trait)
         ->wait(0.5)
         ->click('Finesse: 0')   // 0
         ->wait(0.5)
         ->click('Instinct: +1') // +1 (awareness for protection)
         ->wait(0.5)
         ->click('Presence: +1') // +1 (leadership/protection)
         ->wait(0.5)
         ->click('Knowledge: -1') // -1
         ->waitForText('Trait assignment complete!', 3);
    
    // Navigate to character info section
    $page->click('Next')
         ->waitForText('Character Information', 5);
    
    // Step 6: Verify character stats are calculated correctly
    // Guardian base: 7 HP, 9 Evasion, 6 Stress
    // Dwarf ancestry: typically +1 to damage thresholds
    // Stalwart bonuses: +6 total damage threshold bonus (+1+2+3)
    $page->assertSee('Hit Points: 7') // Guardian base (no ancestry HP bonus)
         ->assertSee('Evasion: 9')    // Guardian base (no trait bonus for evasion in this build)
         ->assertSee('Stress: 6')     // Guardian base
         ->assertSee('Damage Threshold Bonus: +7'); // +1 dwarf + +6 stalwart
    
    // Fill in character name
    $page->type('#character-name', 'Thorgrim Ironshield')
         ->type('#character-pronouns', 'he/him')
         ->waitForText('Character details saved', 3);
    
    // Navigate to equipment selection
    $page->click('Next')
         ->waitForText('Choose Your Equipment', 5);
    
    // Step 7: Select appropriate equipment for guardian
    $page->click('Shield and Sword')     // Classic guardian setup
         ->wait(0.5)
         ->click('Plate Armor')          // Heavy protection
         ->wait(0.5)
         ->click('Guardian\'s Kit')      // Class-appropriate items
         ->waitForText('Equipment selection complete!', 3);
    
    // Navigate to background questions
    $page->click('Next')
         ->waitForText('Background Questions', 5);
    
    // Step 8: Answer guardian-specific background questions
    $page->type('#background-0', 'I failed to protect my younger sister from bandits, and her memory drives my dedication to shielding others.')
         ->wait(0.5)
         ->type('#background-1', 'I must deliver a sacred relic to the Temple of Light, but the path is fraught with undead horrors.')
         ->wait(0.5)
         ->type('#background-2', 'My greatest weakness is my inability to trust others to protect themselves, leading me to overextend.')
         ->waitForText('Background complete!', 3);
    
    // Navigate to experiences
    $page->click('Next')
         ->waitForText('Choose Your Experiences', 5);
    
    // Step 9: Create two experiences
    $page->type('#experience-0', 'Shield Wall Formation')
         ->wait(0.5)
         ->type('#experience-1', 'Protective Instincts')
         ->waitForText('Experiences complete!', 3);
    
    // Navigate to domain cards
    $page->click('Next')
         ->waitForText('Choose Domain Cards', 5);
    
    // Step 10: Select domain cards from Valor and Blade domains
    $page->assertSee('Available Domains: Valor, Blade')
         ->click('Rally') // Level 1 Valor card
         ->wait(0.5)
         ->click('Blade Strike') // Level 1 Blade card
         ->waitForText('Domain cards selected!', 3)
         ->assertSee('2/2 cards selected');
    
    // Navigate to connections
    $page->click('Next')
         ->waitForText('Character Connections', 5);
    
    // Step 11: Answer connection questions
    $page->type('#connection-0', 'I saved your life when you were overwhelmed by goblin raiders, and we\'ve been steadfast allies ever since.')
         ->wait(0.5)
         ->type('#connection-1', 'You gave me a small carved token that reminds me why I fight - I keep it on my shield at all times.')
         ->wait(0.5)
         ->type('#connection-2', 'You told me you were once a common thief, but I believe you\'re a noble soul seeking redemption.')
         ->waitForText('Connections complete!', 3);
    
    // Navigate to final character sheet
    $page->click('Finish Character')
         ->waitForText('Thorgrim Ironshield', 5);
    
    // Final validation: Verify complete character sheet
    $page->assertSee('Thorgrim Ironshield')
         ->assertSee('Guardian')
         ->assertSee('Stalwart')
         ->assertSee('Dwarf')
         ->assertSee('Ridgeborne')
         ->assertSee('Hit Points: 7')
         ->assertSee('Evasion: 9')
         ->assertSee('Stress: 6')
         ->assertSee('Strength: +2')
         ->assertSee('Agility: 0')
         ->assertSee('Finesse: 0')
         ->assertSee('Instinct: +1')
         ->assertSee('Presence: +1')
         ->assertSee('Knowledge: -1')
         ->assertSee('Unwavering')
         ->assertSee('Iron Will')
         ->assertSee('Unrelenting')
         ->assertSee('Undaunted')
         ->assertSee('Rally')
         ->assertSee('Blade Strike')
         ->assertSee('Shield and Sword')
         ->assertSee('Plate Armor')
         ->assertSee('Shield Wall Formation')
         ->assertSee('Protective Instincts');
    
    // Verify navigation to character viewer works
    $page->click('View Character')
         ->waitForText('Thorgrim Ironshield', 5)
         ->assertSee('Guardian • Stalwart')
         ->assertSee('Dwarf Ridgeborne');
    
    // Extract character key from URL to verify database persistence
    $currentUrl = $page->driver->getCurrentURL();
    preg_match('/character-viewer\/([A-Z0-9]{8,})/', $currentUrl, $matches);
    expect($matches)->toHaveCount(2);
    
    $characterKey = $matches[1];
    
    // Verify character was saved correctly in database
    $character = Character::where('character_key', $characterKey)->first();
    expect($character)->not()->toBeNull()
        ->class->toBe('guardian')
        ->subclass->toBe('stalwart')
        ->ancestry->toBe('dwarf')
        ->community->toBe('ridgeborne')
        ->name->toBe('Thorgrim Ironshield')
        ->pronouns->toBe('he/him');
    
    // Verify traits were saved with correct distribution
    $traits = $character->traits()->pluck('trait_value', 'trait_name')->toArray();
    expect($traits)->toBe([
        'agility' => 0,
        'strength' => 2,
        'finesse' => 0,
        'instinct' => 1,
        'presence' => 1,
        'knowledge' => -1,
    ]);
    
    // Verify character stats calculation
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(7);  // Guardian base
    expect($stats->evasion)->toBe(9);     // Guardian base (no trait bonus with this build)
    expect($stats->stress)->toBe(6);      // Guardian base
    
    // Verify damage threshold bonuses are applied
    expect($character->getSubclassDamageThresholdBonus())->toBe(6); // Stalwart +1+2+3
    
    // Verify domain cards were saved
    $domainCards = $character->domainCards()->pluck('card_name')->toArray();
    expect($domainCards)->toContain('Rally')
                       ->toContain('Blade Strike');
    
    // Verify experiences were saved
    $experiences = $character->experiences()->pluck('experience_name')->toArray();
    expect($experiences)->toContain('Shield Wall Formation')
                       ->toContain('Protective Instincts');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guardian stalwart damage threshold stacking validation', function () {
    // Test that Stalwart's damage threshold bonuses stack correctly
    // Foundation: Unwavering (+1)
    // Specialization: Unrelenting (+2) 
    // Mastery: Undaunted (+3)
    // Total: +6 damage threshold bonus
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Guardian', 10)
         ->click('Guardian')
         ->click('Next')
         ->waitForText('Stalwart', 5)
         ->click('Stalwart');
    
    // Verify stalwart features are displayed correctly with stacking bonuses
    $page->assertSee('Unwavering')
         ->assertSee('+1 bonus to your damage thresholds')
         ->assertSee('Unrelenting')
         ->assertSee('+2 bonus to your damage thresholds')
         ->assertSee('Undaunted')
         ->assertSee('+3 bonus to your damage thresholds');
    
    // Verify that these are described as permanent bonuses
    $page->assertSee('permanent +1 bonus')
         ->assertSee('permanent +2 bonus')
         ->assertSee('permanent +3 bonus');
    
    // Verify other defensive features
    $page->assertSee('Iron Will')
         ->assertSee('additional Armor Slot')
         ->assertSee('Partners-in-Arms')
         ->assertSee('ally within Very Close range')
         ->assertSee('Loyal Protector')
         ->assertSee('take the damage instead');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guardian stalwart domain access verification', function () {
    // Test that Guardian class provides correct domain access (Valor + Blade)
    // and Stalwart doesn't modify domain access
    
    $page = visit('/character-builder');
    
    // Navigate to domain cards step
    $page->waitForText('Guardian', 10)
         ->click('Guardian')
         ->click('Next')
         ->waitForText('Stalwart', 5)
         ->click('Stalwart');
    
    // Skip to domain cards section
    for ($i = 0; $i < 7; $i++) { // Skip ancestry, community, traits, info, equipment, background, experiences
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Choose Domain Cards', 5);
    
    // Verify correct domains are available
    $page->assertSee('Available Domains: Valor, Blade')
         ->assertSee('Valor Domain')
         ->assertSee('Blade Domain');
    
    // Verify level 1 cards from both domains are available
    $page->assertPresent('[data-testid="domain-card-rally"]')
         ->assertPresent('[data-testid="domain-card-blade-strike"]');
    
    // Verify domain cards from other domains are NOT available
    $page->assertDontSee('Arcana Domain')
         ->assertDontSee('Grace Domain')
         ->assertDontSee('Bone Domain')
         ->assertDontSee('Midnight Domain');
    
    // Verify can select exactly 2 cards
    $page->click('Rally')
         ->wait(0.5)
         ->assertSee('1/2 cards selected')
         ->click('Blade Strike')
         ->wait(0.5)
         ->assertSee('2/2 cards selected');
    
    // Verify cannot select a third card (buttons should be disabled)
    $remainingCards = $page->elements('[data-testid*="domain-card-"]:not([data-selected])');
    foreach ($remainingCards as $card) {
        expect($card->getAttribute('disabled'))->toBe('true');
    }
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guardian stalwart unstoppable feature integration', function () {
    // Test that Guardian's Unstoppable class feature is properly displayed
    // and integrates with Stalwart's defensive bonuses
    
    $page = visit('/character-builder');
    
    // Navigate through class selection
    $page->waitForText('Guardian', 10)
         ->click('Guardian');
    
    // Verify Guardian class features are shown
    $page->assertSee('Unstoppable')
         ->assertSee('once per long rest')
         ->assertSee('Unstoppable Die')
         ->assertSee('reduce the severity of physical damage')
         ->assertSee('cannot be Restrained or Vulnerable');
    
    // Continue to subclass
    $page->click('Next')
         ->waitForText('Stalwart', 5)
         ->click('Stalwart');
    
    // Verify that Stalwart's defensive features complement Unstoppable
    $page->assertSee('Unwavering')  // Additional damage threshold bonus
         ->assertSee('Iron Will')   // Additional armor protection
         ->assertSee('Partners-in-Arms') // Protect allies too
         ->assertSee('Loyal Protector'); // Ultimate protection sacrifice
    
    // These features work together to create the ultimate defensive character:
    // - Unstoppable: Temporary damage reduction + immunity to conditions
    // - Stalwart: Permanent damage threshold bonuses + armor/ally protection
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
