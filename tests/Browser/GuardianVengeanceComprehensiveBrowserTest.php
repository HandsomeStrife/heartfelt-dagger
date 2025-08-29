<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive Pest 4 browser test for Guardian + Vengeance character creation workflow
 * 
 * This test validates the complete character creation process through UI interactions:
 * - Step 1: Class selection (Guardian)
 * - Step 2: Subclass selection (Vengeance)
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
 * Guardian + Vengeance specific validations:
 * - Guardian base stats (Hit Points: 7, Evasion: 9, Stress: 6)
 * - Vengeance uses Strength as spellcast trait
 * - Retribution Strike: damage reflection when taking damage
 * - Vengeful Mark: tracking and enhanced damage against marked targets
 * - Unyielding Fury: damage scaling based on current damage taken
 * - Relentless Pursuit: movement and positioning bonuses when pursuing marked enemies
 * - Divine Retribution: magical punishment for attacking allies
 * - Wrathful Guardian: area damage when protecting multiple allies
 * - Protector's Wrath Hope feature integration with aggressive protection
 */

test('guardian vengeance complete character creation workflow', function () {
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
         ->assertSee('Armor Mastery')
         ->assertSee('Protector\'s Wrath')
         ->assertSee('Intercept')
         ->assertSee('protection and combat prowess');
    
    // Navigate to subclass selection
    $page->click('Next')
         ->waitForText('Vengeance', 5);
    
    // Step 2: Select Vengeance subclass
    $page->click('Vengeance')
         ->waitForText('Vengeance', 5)
         ->assertSee('Retribution Strike')
         ->assertSee('when you take damage')
         ->assertSee('make an immediate weapon attack')
         ->assertSee('against the creature that damaged you')
         ->assertSee('Vengeful Mark')
         ->assertSee('mark a target for vengeance')
         ->assertSee('enhanced damage against marked targets')
         ->assertSee('track their location')
         ->assertSee('Unyielding Fury')
         ->assertSee('damage increases based on')
         ->assertSee('your current Hit Point damage')
         ->assertSee('Relentless Pursuit')
         ->assertSee('move toward marked enemies')
         ->assertSee('ignore difficult terrain')
         ->assertSee('Divine Retribution')
         ->assertSee('magical punishment')
         ->assertSee('creatures that attack your allies')
         ->assertSee('Wrathful Guardian')
         ->assertSee('area damage when protecting')
         ->assertSee('multiple allies at once')
         ->assertSee('Spellcast Trait: Strength');
    
    // Navigate to ancestry selection
    $page->click('Next')
         ->waitForText('Choose Your Ancestry', 5);
    
    // Step 3: Select Orc ancestry (strong, tribal warrior perfect for vengeance)
    $page->click('Orc')
         ->waitForText('Orc', 5)
         ->assertSee('Strong, tribal warriors')
         ->assertSee('intimidating presence');
    
    // Navigate to community selection
    $page->click('Next')
         ->waitForText('Choose Your Community', 5);
    
    // Step 4: Select Orderborne community (disciplined, structured justice)
    $page->click('Orderborne')
         ->waitForText('Orderborne', 5)
         ->assertSee('Disciplined')
         ->assertSee('faith-based')
         ->assertSee('structured');
    
    // Navigate to trait assignment
    $page->click('Next')
         ->waitForText('Assign Your Traits', 5);
    
    // Step 5: Assign traits with exact DaggerHeart distribution (-1,0,0,+1,+1,+2)
    // Focus on Strength (spellcast trait) and aggressive combat capabilities
    $page->click('Agility: +1')   // +1 (pursuit and positioning)
         ->wait(0.5)
         ->click('Strength: +2')  // +2 (primary spellcast trait)
         ->wait(0.5)
         ->click('Finesse: 0')    // 0
         ->wait(0.5)
         ->click('Instinct: +1')  // +1 (combat awareness)
         ->wait(0.5)
         ->click('Presence: 0')   // 0
         ->wait(0.5)
         ->click('Knowledge: -1') // -1 (less academic focus)
         ->waitForText('Trait assignment complete!', 3);
    
    // Navigate to character info section
    $page->click('Next')
         ->waitForText('Character Information', 5);
    
    // Step 6: Verify character stats are calculated correctly
    // Guardian base: 7 HP, 9 Evasion, 6 Stress
    // Agility +1 affects evasion
    $page->assertSee('Hit Points: 7')  // Guardian base
         ->assertSee('Evasion: 10')    // 9 base + 1 agility
         ->assertSee('Stress: 6')      // Guardian base
         ->assertSee('Spellcast Trait: Strength (+2)');
    
    // Fill in character name
    $page->type('#character-name', 'Grimjaw Oathbreaker')
         ->type('#character-pronouns', 'he/him')
         ->waitForText('Character details saved', 3);
    
    // Navigate to equipment selection
    $page->click('Next')
         ->waitForText('Choose Your Equipment', 5);
    
    // Step 7: Select appropriate equipment for vengeance guardian
    $page->click('Greatsword')        // Heavy weapon for retribution
         ->wait(0.5)
         ->click('Plate Armor')       // Maximum protection
         ->wait(0.5)
         ->click('Shield')            // Additional defense
         ->waitForText('Equipment selection complete!', 3);
    
    // Navigate to background questions
    $page->click('Next')
         ->waitForText('Background Questions', 5);
    
    // Step 8: Answer guardian-specific background questions
    $page->type('#background-0', 'My family was murdered by bandits while I was away, and I have sworn an oath to hunt down every last one of them.')
         ->wait(0.5)
         ->type('#background-1', 'A corrupt noble betrayed my order and sold us out to our enemies, and now I seek justice for my fallen brothers.')
         ->wait(0.5)
         ->type('#background-2', 'I fear that my thirst for vengeance is consuming my ability to show mercy to those who truly deserve it.')
         ->waitForText('Background complete!', 3);
    
    // Navigate to experiences
    $page->click('Next')
         ->waitForText('Choose Your Experiences', 5);
    
    // Step 9: Create two experiences
    $page->type('#experience-0', 'Tracking Criminals')
         ->wait(0.5)
         ->type('#experience-1', 'Intimidation')
         ->waitForText('Experiences complete!', 3);
    
    // Navigate to domain cards
    $page->click('Next')
         ->waitForText('Choose Domain Cards', 5);
    
    // Step 10: Select domain cards from Valor and Blade domains
    $page->assertSee('Available Domains: Valor, Blade')
         ->click('Strike True')       // Level 1 Blade card
         ->wait(0.5)
         ->click('Rallying Cry')      // Level 1 Valor card
         ->waitForText('Domain cards selected!', 3)
         ->assertSee('2/2 cards selected');
    
    // Navigate to connections
    $page->click('Next')
         ->waitForText('Character Connections', 5);
    
    // Step 11: Answer connection questions
    $page->type('#connection-0', 'You witnessed the injustice that set me on my path of vengeance, and your testimony will be crucial when I find justice.')
         ->wait(0.5)
         ->type('#connection-1', 'I saved you from the same enemies who destroyed my life, creating an unbreakable bond between us.')
         ->wait(0.5)
         ->type('#connection-2', 'You keep me grounded when my rage threatens to consume everything good left in my heart.')
         ->waitForText('Connections complete!', 3);
    
    // Navigate to final character sheet
    $page->click('Finish Character')
         ->waitForText('Grimjaw Oathbreaker', 5);
    
    // Final validation: Verify complete character sheet
    $page->assertSee('Grimjaw Oathbreaker')
         ->assertSee('Guardian')
         ->assertSee('Vengeance')
         ->assertSee('Orc')
         ->assertSee('Orderborne')
         ->assertSee('Hit Points: 7')
         ->assertSee('Evasion: 10')
         ->assertSee('Stress: 6')
         ->assertSee('Agility: +1')
         ->assertSee('Strength: +2')
         ->assertSee('Finesse: 0')
         ->assertSee('Instinct: +1')
         ->assertSee('Presence: 0')
         ->assertSee('Knowledge: -1')
         ->assertSee('Armor Mastery')
         ->assertSee('Protector\'s Wrath')
         ->assertSee('Intercept')
         ->assertSee('Retribution Strike')
         ->assertSee('Vengeful Mark')
         ->assertSee('Unyielding Fury')
         ->assertSee('Relentless Pursuit')
         ->assertSee('Divine Retribution')
         ->assertSee('Wrathful Guardian')
         ->assertSee('Strike True')
         ->assertSee('Rallying Cry')
         ->assertSee('Greatsword')
         ->assertSee('Plate Armor')
         ->assertSee('Tracking Criminals')
         ->assertSee('Intimidation');
    
    // Verify navigation to character viewer works
    $page->click('View Character')
         ->waitForText('Grimjaw Oathbreaker', 5)
         ->assertSee('Guardian • Vengeance')
         ->assertSee('Orc Orderborne');
    
    // Extract character key from URL to verify database persistence
    $currentUrl = $page->driver->getCurrentURL();
    preg_match('/character-viewer\/([A-Z0-9]{8,})/', $currentUrl, $matches);
    expect($matches)->toHaveCount(2);
    
    $characterKey = $matches[1];
    
    // Verify character was saved correctly in database
    $character = Character::where('character_key', $characterKey)->first();
    expect($character)->not()->toBeNull()
        ->class->toBe('guardian')
        ->subclass->toBe('vengeance')
        ->ancestry->toBe('orc')
        ->community->toBe('orderborne')
        ->name->toBe('Grimjaw Oathbreaker')
        ->pronouns->toBe('he/him');
    
    // Verify traits were saved with correct distribution
    $traits = $character->traits()->pluck('trait_value', 'trait_name')->toArray();
    expect($traits)->toBe([
        'agility' => 1,
        'strength' => 2,
        'finesse' => 0,
        'instinct' => 1,
        'presence' => 0,
        'knowledge' => -1,
    ]);
    
    // Verify character stats calculation
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(7);  // Guardian base
    expect($stats->evasion)->toBe(10);    // 9 base + 1 agility
    expect($stats->stress)->toBe(6);      // Guardian base
    
    // Verify spellcast trait is correct
    expect($character->getSpellcastTrait())->toBe('strength');
    expect($character->getSpellcastTraitValue())->toBe(2);
    
    // Verify domain cards were saved
    $domainCards = $character->domainCards()->pluck('card_name')->toArray();
    expect($domainCards)->toContain('Strike True')
                       ->toContain('Rallying Cry');
    
    // Verify experiences were saved
    $experiences = $character->experiences()->pluck('experience_name')->toArray();
    expect($experiences)->toContain('Tracking Criminals')
                       ->toContain('Intimidation');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guardian vengeance retribution mechanics validation', function () {
    // Test that Vengeance's retribution and marking mechanics are properly displayed
    // and damage reflection/escalation is explained
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Guardian', 10)
         ->click('Guardian')
         ->click('Next')
         ->waitForText('Vengeance', 5)
         ->click('Vengeance');
    
    // Verify retribution mechanics
    $page->assertSee('Retribution Strike')
         ->assertSee('when you take damage')
         ->assertSee('make an immediate weapon attack')
         ->assertSee('against the creature that damaged you')
         ->assertSee('without spending an action');
    
    // Verify marking system
    $page->assertSee('Vengeful Mark')
         ->assertSee('mark a target for vengeance')
         ->assertSee('enhanced damage against marked targets')
         ->assertSee('track their location')
         ->assertSee('sense their presence');
    
    // Verify damage escalation
    $page->assertSee('Unyielding Fury')
         ->assertSee('damage increases based on')
         ->assertSee('your current Hit Point damage')
         ->assertSee('more wounded you are')
         ->assertSee('more devastating your attacks');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guardian vengeance pursuit mechanics validation', function () {
    // Test that Vengeance's pursuit and area protection abilities are displayed
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Guardian', 10)
         ->click('Guardian')
         ->click('Next')
         ->waitForText('Vengeance', 5)
         ->click('Vengeance');
    
    // Verify pursuit features
    $page->assertSee('Relentless Pursuit')
         ->assertSee('move toward marked enemies')
         ->assertSee('ignore difficult terrain')
         ->assertSee('enhanced movement speed')
         ->assertSee('when pursuing vengeance');
    
    // Verify divine punishment
    $page->assertSee('Divine Retribution')
         ->assertSee('magical punishment')
         ->assertSee('creatures that attack your allies')
         ->assertSee('automatic retaliation')
         ->assertSee('without your direct action');
    
    $page->assertSee('Wrathful Guardian')
         ->assertSee('area damage when protecting')
         ->assertSee('multiple allies at once')
         ->assertSee('enemies who threaten your group')
         ->assertSee('face your wrath');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guardian vengeance protectors wrath integration validation', function () {
    // Test that Vengeance features integrate with Guardian's Protector's Wrath
    
    $page = visit('/character-builder');
    
    // Navigate through class selection
    $page->waitForText('Guardian', 10)
         ->click('Guardian');
    
    // Verify Guardian's Protector's Wrath Hope feature is shown
    $page->assertSee('Protector\'s Wrath')
         ->assertSee('Spend 3 Hope')
         ->assertSee('deal damage to all adversaries')
         ->assertSee('within Close range of you');
    
    // Continue to subclass
    $page->click('Next')
         ->waitForText('Vengeance', 5)
         ->click('Vengeance');
    
    // Verify Vengeance enhances protection with aggressive retaliation
    $page->assertSee('Retribution Strike')    // Automatic counterattacks
         ->assertSee('Divine Retribution')    // Magical ally protection
         ->assertSee('Wrathful Guardian');    // Area protection enhancement
    
    // The synergy: Protector's Wrath for deliberate area damage,
    // Vengeance provides automatic retaliation and enhanced pursuit
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guardian vengeance armor mastery integration validation', function () {
    // Test that Vengeance features work with Guardian's defensive foundation
    
    $page = visit('/character-builder');
    
    // Navigate through class selection
    $page->waitForText('Guardian', 10)
         ->click('Guardian');
    
    // Verify Guardian's defensive features
    $page->assertSee('Armor Mastery')
         ->assertSee('reduce damage from attacks')
         ->assertSee('heavy armor proficiency')
         ->assertSee('Intercept')
         ->assertSee('take damage meant for an ally');
    
    // Continue to subclass
    $page->click('Next')
         ->waitForText('Vengeance', 5)
         ->click('Vengeance');
    
    // Verify Vengeance turns defense into offense
    $page->assertSee('Retribution Strike')    // Turn taking damage into attacks
         ->assertSee('Unyielding Fury')       // Damage taken increases damage dealt
         ->assertSee('Divine Retribution');   // Protect allies through punishment
    
    // The synergy: Armor Mastery and Intercept allow surviving to trigger retribution,
    // Vengeance turns damage taken into increased offensive capability
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guardian vengeance domain access verification', function () {
    // Test that Guardian class provides correct domain access (Valor + Blade)
    // and Vengeance doesn't modify domain access
    
    $page = visit('/character-builder');
    
    // Navigate to domain cards step
    $page->waitForText('Guardian', 10)
         ->click('Guardian')
         ->click('Next')
         ->waitForText('Vengeance', 5)
         ->click('Vengeance');
    
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
    $page->assertPresent('[data-testid="domain-card-strike-true"]')
         ->assertPresent('[data-testid="domain-card-rallying-cry"]');
    
    // Verify domain cards from other domains are NOT available
    $page->assertDontSee('Arcana Domain')
         ->assertDontSee('Midnight Domain')
         ->assertDontSee('Grace Domain')
         ->assertDontSee('Sage Domain');
    
    // Verify can select exactly 2 cards
    $page->click('Strike True')
         ->wait(0.5)
         ->assertSee('1/2 cards selected')
         ->click('Rallying Cry')
         ->wait(0.5)
         ->assertSee('2/2 cards selected');
    
    // Verify cannot select a third card (buttons should be disabled)
    $remainingCards = $page->elements('[data-testid*="domain-card-"]:not([data-selected])');
    foreach ($remainingCards as $card) {
        expect($card->getAttribute('disabled'))->toBe('true');
    }
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guardian vengeance damage scaling validation', function () {
    // Test that Vengeance's damage scaling based on damage taken is properly explained
    // Unyielding Fury creates a risk/reward mechanic
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Guardian', 10)
         ->click('Guardian')
         ->click('Next')
         ->waitForText('Vengeance', 5)
         ->click('Vengeance');
    
    // Verify damage scaling is clearly explained
    $page->assertSee('Unyielding Fury')
         ->assertSee('damage increases based on')
         ->assertSee('your current Hit Point damage')
         ->assertSee('more wounded you are')
         ->assertSee('more devastating your attacks')
         ->assertSee('risk equals reward');
    
    // This creates interesting tactical decisions:
    // - Take some damage to increase offensive power
    // - Balance survivability with damage output
    // - Synergizes with Guardian's high HP and defensive abilities
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guardian vengeance spellcast trait validation', function () {
    // Test that Vengeance correctly assigns Strength as spellcast trait
    // (emphasizing physical power for retribution magic)
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Guardian', 10)
         ->click('Guardian')
         ->click('Next')
         ->waitForText('Vengeance', 5)
         ->click('Vengeance');
    
    // Verify spellcast trait is displayed
    $page->assertSee('Spellcast Trait: Strength')
         ->assertSee('Your retribution magic uses Strength');
    
    // Navigate to trait assignment to verify integration
    for ($i = 0; $i < 3; $i++) { // Skip ancestry, community
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Assign Your Traits', 5);
    
    // Verify Strength is highlighted as spellcast trait
    $page->assertSee('Strength (Spellcast)')
         ->assertSee('This is your primary retribution power trait');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
