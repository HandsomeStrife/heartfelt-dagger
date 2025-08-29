<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive Pest 4 browser test for Ranger + Beastbound character creation workflow
 * 
 * This test validates the complete character creation process through UI interactions:
 * - Step 1: Class selection (Ranger)
 * - Step 2: Subclass selection (Beastbound)
 * - Step 3: Ancestry selection  
 * - Step 4: Community selection
 * - Step 5: Trait assignment (validates exact -1,0,0,+1,+1,+2 distribution)
 * - Step 6: Character stats verification
 * - Step 7: Equipment selection
 * - Step 8: Background questions
 * - Step 9: Experiences
 * - Step 10: Domain cards (Sage + Bone access)
 * - Step 11: Connections
 * - Step 12: Companion selection and customization
 * 
 * Ranger + Beastbound specific validations:
 * - Ranger base stats (Hit Points: 5, Evasion: 9, Stress: 6)
 * - Beastbound uses Agility as spellcast trait
 * - Companion mechanics: animal companion selection and companion sheet
 * - Expert Training: additional companion level-up options
 * - Battle-Bonded: +2 Evasion bonus when near companion
 * - Advanced Training: multiple companion improvements
 * - Loyal Friend: damage switching mechanics
 * - Ranger's Focus integration with companion tactics
 */

test('ranger beastbound complete character creation workflow', function () {
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
         ->assertSee('Ranger\'s Focus')
         ->assertSee('nature-based tracking');
    
    // Navigate to subclass selection
    $page->click('Next')
         ->waitForText('Beastbound', 5);
    
    // Step 2: Select Beastbound subclass
    $page->click('Beastbound')
         ->waitForText('Beastbound', 5)
         ->assertSee('Companion')
         ->assertSee('animal companion of your choice')
         ->assertSee('Ranger Companion sheet')
         ->assertSee('level up your character')
         ->assertSee('Expert Training')
         ->assertSee('additional level-up option for your companion')
         ->assertSee('Battle-Bonded')
         ->assertSee('+2 bonus to your Evasion')
         ->assertSee('companion\'s Melee range')
         ->assertSee('Advanced Training')
         ->assertSee('two additional level-up options')
         ->assertSee('Loyal Friend')
         ->assertSee('take that damage instead')
         ->assertSee('Spellcast Trait: Agility');
    
    // Navigate to ancestry selection
    $page->click('Next')
         ->waitForText('Choose Your Ancestry', 5);
    
    // Step 3: Select Human ancestry (versatile for ranger)
    $page->click('Human')
         ->waitForText('Human', 5)
         ->assertSee('Versatile and adaptable')
         ->assertSee('+2 Stress');
    
    // Navigate to community selection
    $page->click('Next')
         ->waitForText('Choose Your Community', 5);
    
    // Step 4: Select Wildborne community (perfect for ranger)
    $page->click('Wildborne')
         ->waitForText('Wildborne', 5)
         ->assertSee('Wilderness-dwelling')
         ->assertSee('survival-focused');
    
    // Navigate to trait assignment
    $page->click('Next')
         ->waitForText('Assign Your Traits', 5);
    
    // Step 5: Assign traits with exact DaggerHeart distribution (-1,0,0,+1,+1,+2)
    // Focus on Agility (spellcast trait) and survival capabilities
    $page->click('Agility: +2')   // +2 (primary spellcast trait)
         ->wait(0.5)
         ->click('Strength: +1')  // +1 (physical capability)
         ->wait(0.5)
         ->click('Finesse: +1')   // +1 (precision for archery)
         ->wait(0.5)
         ->click('Instinct: 0')   // 0
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
    // Human ancestry: +2 Stress
    // Agility +2 affects evasion
    $page->assertSee('Hit Points: 5')  // Ranger base
         ->assertSee('Evasion: 11')    // 9 base + 2 agility
         ->assertSee('Stress: 8')      // 6 base + 2 human
         ->assertSee('Spellcast Trait: Agility (+2)');
    
    // Fill in character name
    $page->type('#character-name', 'Kael Beastfriend')
         ->type('#character-pronouns', 'he/him')
         ->waitForText('Character details saved', 3);
    
    // Navigate to equipment selection
    $page->click('Next')
         ->waitForText('Choose Your Equipment', 5);
    
    // Step 7: Select appropriate equipment for ranger
    $page->click('Longbow')           // Ranged weapon for hunting
         ->wait(0.5)
         ->click('Leather Armor')     // Light armor for mobility
         ->wait(0.5)
         ->click('Survival Kit')      // Ranger-specific equipment
         ->waitForText('Equipment selection complete!', 3);
    
    // Navigate to background questions
    $page->click('Next')
         ->waitForText('Background Questions', 5);
    
    // Step 8: Answer ranger-specific background questions
    $page->type('#background-0', 'I was raised by wolves after my village was destroyed, learning to communicate with animals as my family.')
         ->wait(0.5)
         ->type('#background-1', 'I must track down the poachers who killed my wolf pack and bring them to justice in the wilderness court.')
         ->wait(0.5)
         ->type('#background-2', 'I fear that my deep connection to animals makes me too wild to live among civilized people.')
         ->waitForText('Background complete!', 3);
    
    // Navigate to experiences
    $page->click('Next')
         ->waitForText('Choose Your Experiences', 5);
    
    // Step 9: Create two experiences
    $page->type('#experience-0', 'Animal Training')
         ->wait(0.5)
         ->type('#experience-1', 'Wilderness Survival')
         ->waitForText('Experiences complete!', 3);
    
    // Navigate to domain cards
    $page->click('Next')
         ->waitForText('Choose Domain Cards', 5);
    
    // Step 10: Select domain cards from Sage and Bone domains
    $page->assertSee('Available Domains: Sage, Bone')
         ->click('Heal')              // Level 1 Sage card
         ->wait(0.5)
         ->click('Scare')             // Level 1 Bone card
         ->waitForText('Domain cards selected!', 3)
         ->assertSee('2/2 cards selected');
    
    // Navigate to connections
    $page->click('Next')
         ->waitForText('Character Connections', 5);
    
    // Step 11: Answer connection questions
    $page->type('#connection-0', 'You helped me save an injured hawk, and now we both understand the sacred bond between protector and protected.')
         ->wait(0.5)
         ->type('#connection-1', 'I taught you how to move silently through the forest, a skill that has saved your life multiple times.')
         ->wait(0.5)
         ->type('#connection-2', 'We both know that I can speak with animals, but you\'ve sworn to keep this gift secret from those who would exploit it.')
         ->waitForText('Connections complete!', 3);
    
    // Navigate to companion selection
    $page->click('Next')
         ->waitForText('Choose Your Companion', 5);
    
    // Step 12: Select and customize animal companion
    $page->assertSee('Animal Companion')
         ->assertSee('Choose your companion type')
         ->click('Wolf')
         ->waitForText('Wolf selected', 3)
         ->assertSee('Pack Hunter')
         ->assertSee('Loyal')
         ->assertSee('Fierce');
    
    // Customize companion
    $page->type('#companion-name', 'Shadowpaw')
         ->waitForText('Companion named', 2);
    
    // Navigate to final character sheet
    $page->click('Finish Character')
         ->waitForText('Kael Beastfriend', 5);
    
    // Final validation: Verify complete character sheet
    $page->assertSee('Kael Beastfriend')
         ->assertSee('Ranger')
         ->assertSee('Beastbound')
         ->assertSee('Human')
         ->assertSee('Wildborne')
         ->assertSee('Hit Points: 5')
         ->assertSee('Evasion: 11')
         ->assertSee('Stress: 8')
         ->assertSee('Agility: +2')
         ->assertSee('Strength: +1')
         ->assertSee('Finesse: +1')
         ->assertSee('Instinct: 0')
         ->assertSee('Presence: 0')
         ->assertSee('Knowledge: -1')
         ->assertSee('Companion: Shadowpaw (Wolf)')
         ->assertSee('Expert Training')
         ->assertSee('Battle-Bonded')
         ->assertSee('Advanced Training')
         ->assertSee('Loyal Friend')
         ->assertSee('Heal')
         ->assertSee('Scare')
         ->assertSee('Longbow')
         ->assertSee('Leather Armor')
         ->assertSee('Animal Training')
         ->assertSee('Wilderness Survival');
    
    // Verify navigation to character viewer works
    $page->click('View Character')
         ->waitForText('Kael Beastfriend', 5)
         ->assertSee('Ranger • Beastbound')
         ->assertSee('Human Wildborne')
         ->assertSee('Shadowpaw');
    
    // Extract character key from URL to verify database persistence
    $currentUrl = $page->driver->getCurrentURL();
    preg_match('/character-viewer\/([A-Z0-9]{8,})/', $currentUrl, $matches);
    expect($matches)->toHaveCount(2);
    
    $characterKey = $matches[1];
    
    // Verify character was saved correctly in database
    $character = Character::where('character_key', $characterKey)->first();
    expect($character)->not()->toBeNull()
        ->class->toBe('ranger')
        ->subclass->toBe('beastbound')
        ->ancestry->toBe('human')
        ->community->toBe('wildborne')
        ->name->toBe('Kael Beastfriend')
        ->pronouns->toBe('he/him');
    
    // Verify traits were saved with correct distribution
    $traits = $character->traits()->pluck('trait_value', 'trait_name')->toArray();
    expect($traits)->toBe([
        'agility' => 2,
        'strength' => 1,
        'finesse' => 1,
        'instinct' => 0,
        'presence' => 0,
        'knowledge' => -1,
    ]);
    
    // Verify character stats calculation
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(5);  // Ranger base
    expect($stats->evasion)->toBe(11);    // 9 base + 2 agility
    expect($stats->stress)->toBe(8);      // 6 base + 2 human
    
    // Verify spellcast trait is correct
    expect($character->getSpellcastTrait())->toBe('agility');
    expect($character->getSpellcastTraitValue())->toBe(2);
    
    // Verify domain cards were saved
    $domainCards = $character->domainCards()->pluck('card_name')->toArray();
    expect($domainCards)->toContain('Heal')
                       ->toContain('Scare');
    
    // Verify experiences were saved
    $experiences = $character->experiences()->pluck('experience_name')->toArray();
    expect($experiences)->toContain('Animal Training')
                       ->toContain('Wilderness Survival');
    
    // Verify companion was saved
    expect($character->getCompanionName())->toBe('Shadowpaw');
    expect($character->getCompanionType())->toBe('Wolf');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('ranger beastbound companion mechanics validation', function () {
    // Test that Beastbound companion mechanics are properly displayed
    // and companion selection/customization works
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Ranger', 10)
         ->click('Ranger')
         ->click('Next')
         ->waitForText('Beastbound', 5)
         ->click('Beastbound');
    
    // Verify companion features are described
    $page->assertSee('Companion')
         ->assertSee('animal companion of your choice')
         ->assertSee('at the GM\'s discretion')
         ->assertSee('Ranger Companion sheet')
         ->assertSee('level up your character')
         ->assertSee('choose a level-up option for your companion');
    
    // Verify progression features
    $page->assertSee('Expert Training')
         ->assertSee('additional level-up option for your companion');
    
    $page->assertSee('Battle-Bonded')
         ->assertSee('adversary attacks you')
         ->assertSee('within your companion\'s Melee range')
         ->assertSee('+2 bonus to your Evasion');
    
    $page->assertSee('Advanced Training')
         ->assertSee('two additional level-up options for your companion');
    
    $page->assertSee('Loyal Friend')
         ->assertSee('once per long rest')
         ->assertSee('mark your companion\'s last Stress')
         ->assertSee('your last Hit Point')
         ->assertSee('within Close range')
         ->assertSee('take that damage instead');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('ranger beastbound focus integration validation', function () {
    // Test that Ranger's Focus feature integrates with Beastbound companion tactics
    
    $page = visit('/character-builder');
    
    // Navigate through class selection
    $page->waitForText('Ranger', 10)
         ->click('Ranger');
    
    // Verify Ranger's Focus feature is shown
    $page->assertSee('Ranger\'s Focus')
         ->assertSee('Spend 1 Hope')
         ->assertSee('make an attack against a target')
         ->assertSee('make the attack\'s target your Focus')
         ->assertSee('know precisely what direction they are in')
         ->assertSee('deal damage to them, they must mark a Stress')
         ->assertSee('fail an attack against them')
         ->assertSee('reroll your Duality Dice');
    
    // Continue to subclass
    $page->click('Next')
         ->waitForText('Beastbound', 5)
         ->click('Beastbound');
    
    // Verify Beastbound features complement Focus mechanics
    $page->assertSee('Battle-Bonded')  // Companion provides defensive positioning
         ->assertSee('Loyal Friend');   // Companion can sacrifice for protection
    
    // The synergy: Ranger's Focus marks priority targets,
    // Companion provides tactical positioning and protection
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('ranger beastbound domain access verification', function () {
    // Test that Ranger class provides correct domain access (Sage + Bone)
    // and Beastbound doesn't modify domain access
    
    $page = visit('/character-builder');
    
    // Navigate to domain cards step
    $page->waitForText('Ranger', 10)
         ->click('Ranger')
         ->click('Next')
         ->waitForText('Beastbound', 5)
         ->click('Beastbound');
    
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
    $page->assertPresent('[data-testid="domain-card-heal"]')
         ->assertPresent('[data-testid="domain-card-scare"]');
    
    // Verify domain cards from other domains are NOT available
    $page->assertDontSee('Arcana Domain')
         ->assertDontSee('Grace Domain')
         ->assertDontSee('Valor Domain')
         ->assertDontSee('Midnight Domain');
    
    // Verify can select exactly 2 cards
    $page->click('Heal')
         ->wait(0.5)
         ->assertSee('1/2 cards selected')
         ->click('Scare')
         ->wait(0.5)
         ->assertSee('2/2 cards selected');
    
    // Verify cannot select a third card (buttons should be disabled)
    $remainingCards = $page->elements('[data-testid*="domain-card-"]:not([data-selected])');
    foreach ($remainingCards as $card) {
        expect($card->getAttribute('disabled'))->toBe('true');
    }
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('ranger beastbound spellcast trait validation', function () {
    // Test that Beastbound correctly assigns Agility as spellcast trait
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Ranger', 10)
         ->click('Ranger')
         ->click('Next')
         ->waitForText('Beastbound', 5)
         ->click('Beastbound');
    
    // Verify spellcast trait is displayed
    $page->assertSee('Spellcast Trait: Agility')
         ->assertSee('Your nature magic uses Agility');
    
    // Navigate to trait assignment to verify integration
    for ($i = 0; $i < 3; $i++) { // Skip ancestry, community
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Assign Your Traits', 5);
    
    // Verify Agility is highlighted as spellcast trait
    $page->assertSee('Agility (Spellcast)')
         ->assertSee('This is your primary nature magic trait');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
