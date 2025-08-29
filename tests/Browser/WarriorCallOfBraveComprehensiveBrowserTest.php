<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive Pest 4 browser test for Warrior + Call of the Brave character creation workflow
 * 
 * This test validates the complete character creation process through UI interactions:
 * - Step 1: Class selection (Warrior)
 * - Step 2: Subclass selection (Call of the Brave)
 * - Step 3: Ancestry selection  
 * - Step 4: Community selection
 * - Step 5: Trait assignment (validates exact -1,0,0,+1,+1,+2 distribution)
 * - Step 6: Character stats verification
 * - Step 7: Equipment selection
 * - Step 8: Background questions
 * - Step 9: Experiences
 * - Step 10: Domain cards (Blade + Bone access)
 * - Step 11: Connections
 * 
 * Warrior + Call of the Brave specific validations:
 * - Warrior base stats (Hit Points: 6, Evasion: 11, Stress: 6)
 * - Call of the Brave uses Presence as spellcast trait
 * - Inspiring Presence: boost ally morale and capabilities
 * - Battle Cry: rally allies in combat with tactical advantages
 * - Heroic Leadership: coordinate group tactics and maneuvers
 * - Stand Together: protective formation and mutual aid
 * - Legendary Courage: fear resistance and inspiration spreading
 * - Commander's Will: ultimate battlefield leadership
 * - Second Wind and Indomitable Spirit class feature integration
 */

test('warrior call of brave complete character creation workflow', function () {
    // Start at character builder main page
    $page = visit('/character-builder');
    
    // Verify initial page load
    $page->assertSee('Character Builder')
         ->waitForText('Warrior', 10);
    
    // Step 1: Select Warrior class
    $page->click('Warrior')
         ->waitForText('Warrior', 5)
         ->assertSee('Blade')
         ->assertSee('Bone')
         ->assertSee('Starting Evasion: 11')
         ->assertSee('Starting Hit Points: 6')
         ->assertSee('Second Wind')
         ->assertSee('Indomitable Spirit')
         ->assertSee('martial prowess and resilience');
    
    // Navigate to subclass selection
    $page->click('Next')
         ->waitForText('Call of the Brave', 5);
    
    // Step 2: Select Call of the Brave subclass
    $page->click('Call of the Brave')
         ->waitForText('Call of the Brave', 5)
         ->assertSee('Inspiring Presence')
         ->assertSee('boost ally morale')
         ->assertSee('enhance their capabilities')
         ->assertSee('courage and determination')
         ->assertSee('Battle Cry')
         ->assertSee('rally allies in combat')
         ->assertSee('tactical advantages')
         ->assertSee('coordinated attacks')
         ->assertSee('Heroic Leadership')
         ->assertSee('coordinate group tactics')
         ->assertSee('battlefield maneuvers')
         ->assertSee('strategic positioning')
         ->assertSee('Stand Together')
         ->assertSee('protective formation')
         ->assertSee('mutual aid')
         ->assertSee('shield wall tactics')
         ->assertSee('Legendary Courage')
         ->assertSee('fear resistance')
         ->assertSee('inspiration spreading')
         ->assertSee('immune to terror')
         ->assertSee('Commander\'s Will')
         ->assertSee('ultimate battlefield leadership')
         ->assertSee('master tactician')
         ->assertSee('Spellcast Trait: Presence');
    
    // Navigate to ancestry selection
    $page->click('Next')
         ->waitForText('Choose Your Ancestry', 5);
    
    // Step 3: Select Human ancestry (natural leaders and versatile)
    $page->click('Human')
         ->waitForText('Human', 5)
         ->assertSee('Versatile and adaptable')
         ->assertSee('+2 Stress');
    
    // Navigate to community selection
    $page->click('Next')
         ->waitForText('Choose Your Community', 5);
    
    // Step 4: Select Orderborne community (structured military background)
    $page->click('Orderborne')
         ->waitForText('Orderborne', 5)
         ->assertSee('Disciplined')
         ->assertSee('faith-based')
         ->assertSee('structured');
    
    // Navigate to trait assignment
    $page->click('Next')
         ->waitForText('Assign Your Traits', 5);
    
    // Step 5: Assign traits with exact DaggerHeart distribution (-1,0,0,+1,+1,+2)
    // Focus on Presence (spellcast trait) and leadership capabilities
    $page->click('Agility: 0')     // 0
         ->wait(0.5)
         ->click('Strength: +1')   // +1 (martial capability)
         ->wait(0.5)
         ->click('Finesse: +1')    // +1 (tactical precision)
         ->wait(0.5)
         ->click('Instinct: 0')    // 0
         ->wait(0.5)
         ->click('Presence: +2')   // +2 (primary spellcast trait)
         ->wait(0.5)
         ->click('Knowledge: -1')  // -1 (less academic focus)
         ->waitForText('Trait assignment complete!', 3);
    
    // Navigate to character info section
    $page->click('Next')
         ->waitForText('Character Information', 5);
    
    // Step 6: Verify character stats are calculated correctly
    // Warrior base: 6 HP, 11 Evasion, 6 Stress
    // Human ancestry: +2 Stress
    $page->assertSee('Hit Points: 6')  // Warrior base
         ->assertSee('Evasion: 11')    // Warrior base (no agility bonus)
         ->assertSee('Stress: 8')      // 6 base + 2 human
         ->assertSee('Spellcast Trait: Presence (+2)');
    
    // Fill in character name
    $page->type('#character-name', 'Captain Valor Ironheart')
         ->type('#character-pronouns', 'he/him')
         ->waitForText('Character details saved', 3);
    
    // Navigate to equipment selection
    $page->click('Next')
         ->waitForText('Choose Your Equipment', 5);
    
    // Step 7: Select appropriate equipment for leadership warrior
    $page->click('Sword and Shield') // Classic leadership weapons
         ->wait(0.5)
         ->click('Plate Armor')      // Heavy protection for frontline leader
         ->wait(0.5)
         ->click('Battle Standard')  // Leadership symbol
         ->waitForText('Equipment selection complete!', 3);
    
    // Navigate to background questions
    $page->click('Next')
         ->waitForText('Background Questions', 5);
    
    // Step 8: Answer warrior-specific background questions
    $page->type('#background-0', 'I led a small militia to victory against impossible odds, earning the loyalty and respect of soldiers across the realm.')
         ->wait(0.5)
         ->type('#background-1', 'A great war is coming, and scattered forces need a leader who can unite them against the approaching darkness.')
         ->wait(0.5)
         ->type('#background-2', 'I fear that my desire to protect everyone will lead me to make impossible promises I cannot keep.')
         ->waitForText('Background complete!', 3);
    
    // Navigate to experiences
    $page->click('Next')
         ->waitForText('Choose Your Experiences', 5);
    
    // Step 9: Create two experiences
    $page->type('#experience-0', 'Military Strategy')
         ->wait(0.5)
         ->type('#experience-1', 'Inspirational Speaking')
         ->waitForText('Experiences complete!', 3);
    
    // Navigate to domain cards
    $page->click('Next')
         ->waitForText('Choose Domain Cards', 5);
    
    // Step 10: Select domain cards from Blade and Bone domains
    $page->assertSee('Available Domains: Blade, Bone')
         ->click('Command Strike')    // Level 1 Blade card
         ->wait(0.5)
         ->click('Rally Troops')      // Level 1 Bone card
         ->waitForText('Domain cards selected!', 3)
         ->assertSee('2/2 cards selected');
    
    // Navigate to connections
    $page->click('Next')
         ->waitForText('Character Connections', 5);
    
    // Step 11: Answer connection questions
    $page->type('#connection-0', 'I trained you in combat and tactics, and now you serve as my most trusted lieutenant on the battlefield.')
         ->wait(0.5)
         ->type('#connection-1', 'You saved my life when I was wounded and surrounded, carrying me to safety despite great personal risk.')
         ->wait(0.5)
         ->type('#connection-2', 'We both swore an oath to protect the innocent, and our combined strength makes us nearly unstoppable in battle.')
         ->waitForText('Connections complete!', 3);
    
    // Navigate to final character sheet
    $page->click('Finish Character')
         ->waitForText('Captain Valor Ironheart', 5);
    
    // Final validation: Verify complete character sheet
    $page->assertSee('Captain Valor Ironheart')
         ->assertSee('Warrior')
         ->assertSee('Call of the Brave')
         ->assertSee('Human')
         ->assertSee('Orderborne')
         ->assertSee('Hit Points: 6')
         ->assertSee('Evasion: 11')
         ->assertSee('Stress: 8')
         ->assertSee('Agility: 0')
         ->assertSee('Strength: +1')
         ->assertSee('Finesse: +1')
         ->assertSee('Instinct: 0')
         ->assertSee('Presence: +2')
         ->assertSee('Knowledge: -1')
         ->assertSee('Second Wind')
         ->assertSee('Indomitable Spirit')
         ->assertSee('Inspiring Presence')
         ->assertSee('Battle Cry')
         ->assertSee('Heroic Leadership')
         ->assertSee('Stand Together')
         ->assertSee('Legendary Courage')
         ->assertSee('Commander\'s Will')
         ->assertSee('Command Strike')
         ->assertSee('Rally Troops')
         ->assertSee('Sword and Shield')
         ->assertSee('Plate Armor')
         ->assertSee('Military Strategy')
         ->assertSee('Inspirational Speaking');
    
    // Verify navigation to character viewer works
    $page->click('View Character')
         ->waitForText('Captain Valor Ironheart', 5)
         ->assertSee('Warrior • Call of the Brave')
         ->assertSee('Human Orderborne');
    
    // Extract character key from URL to verify database persistence
    $currentUrl = $page->driver->getCurrentURL();
    preg_match('/character-viewer\/([A-Z0-9]{8,})/', $currentUrl, $matches);
    expect($matches)->toHaveCount(2);
    
    $characterKey = $matches[1];
    
    // Verify character was saved correctly in database
    $character = Character::where('character_key', $characterKey)->first();
    expect($character)->not()->toBeNull()
        ->class->toBe('warrior')
        ->subclass->toBe('call of the brave')
        ->ancestry->toBe('human')
        ->community->toBe('orderborne')
        ->name->toBe('Captain Valor Ironheart')
        ->pronouns->toBe('he/him');
    
    // Verify traits were saved with correct distribution
    $traits = $character->traits()->pluck('trait_value', 'trait_name')->toArray();
    expect($traits)->toBe([
        'agility' => 0,
        'strength' => 1,
        'finesse' => 1,
        'instinct' => 0,
        'presence' => 2,
        'knowledge' => -1,
    ]);
    
    // Verify character stats calculation
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(6);  // Warrior base
    expect($stats->evasion)->toBe(11);    // Warrior base (no agility bonus)
    expect($stats->stress)->toBe(8);      // 6 base + 2 human
    
    // Verify spellcast trait is correct
    expect($character->getSpellcastTrait())->toBe('presence');
    expect($character->getSpellcastTraitValue())->toBe(2);
    
    // Verify domain cards were saved
    $domainCards = $character->domainCards()->pluck('card_name')->toArray();
    expect($domainCards)->toContain('Command Strike')
                       ->toContain('Rally Troops');
    
    // Verify experiences were saved
    $experiences = $character->experiences()->pluck('experience_name')->toArray();
    expect($experiences)->toContain('Military Strategy')
                       ->toContain('Inspirational Speaking');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('warrior call of brave leadership mechanics validation', function () {
    // Test that Call of the Brave's leadership and inspiration mechanics are properly displayed
    // and ally enhancement effects are explained
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Warrior', 10)
         ->click('Warrior')
         ->click('Next')
         ->waitForText('Call of the Brave', 5)
         ->click('Call of the Brave');
    
    // Verify leadership mechanics
    $page->assertSee('Inspiring Presence')
         ->assertSee('boost ally morale')
         ->assertSee('enhance their capabilities')
         ->assertSee('courage and determination')
         ->assertSee('passive aura effect');
    
    // Verify battle coordination
    $page->assertSee('Battle Cry')
         ->assertSee('rally allies in combat')
         ->assertSee('tactical advantages')
         ->assertSee('coordinated attacks')
         ->assertSee('temporary bonuses');
    
    // Verify tactical command
    $page->assertSee('Heroic Leadership')
         ->assertSee('coordinate group tactics')
         ->assertSee('battlefield maneuvers')
         ->assertSee('strategic positioning')
         ->assertSee('formation fighting');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('warrior call of brave protection mechanics validation', function () {
    // Test that Call of the Brave's protective and courage abilities are displayed
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Warrior', 10)
         ->click('Warrior')
         ->click('Next')
         ->waitForText('Call of the Brave', 5)
         ->click('Call of the Brave');
    
    // Verify protection features
    $page->assertSee('Stand Together')
         ->assertSee('protective formation')
         ->assertSee('mutual aid')
         ->assertSee('shield wall tactics')
         ->assertSee('defensive coordination');
    
    // Verify courage mechanics
    $page->assertSee('Legendary Courage')
         ->assertSee('fear resistance')
         ->assertSee('inspiration spreading')
         ->assertSee('immune to terror')
         ->assertSee('contagious bravery');
    
    $page->assertSee('Commander\'s Will')
         ->assertSee('ultimate battlefield leadership')
         ->assertSee('master tactician')
         ->assertSee('inspire beyond limits')
         ->assertSee('legendary battlefield presence');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('warrior call of brave second wind integration validation', function () {
    // Test that Call of the Brave features integrate with Warrior's Second Wind
    
    $page = visit('/character-builder');
    
    // Navigate through class selection
    $page->waitForText('Warrior', 10)
         ->click('Warrior');
    
    // Verify Warrior's Second Wind features are shown
    $page->assertSee('Second Wind')
         ->assertSee('recover from exhaustion')
         ->assertSee('regain stamina')
         ->assertSee('Indomitable Spirit')
         ->assertSee('resist mental effects')
         ->assertSee('unbreakable will');
    
    // Continue to subclass
    $page->click('Next')
         ->waitForText('Call of the Brave', 5)
         ->click('Call of the Brave');
    
    // Verify Call of the Brave enhances resilience with leadership
    $page->assertSee('Inspiring Presence')   // Enhance ally capabilities
         ->assertSee('Legendary Courage')    // Spread courage and fear resistance
         ->assertSee('Stand Together');      // Mutual aid and protection
    
    // The synergy: Second Wind for personal resilience,
    // Call of the Brave spreads resilience and courage to allies
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('warrior call of brave indomitable spirit integration validation', function () {
    // Test that Call of the Brave features enhance Warrior's Indomitable Spirit
    
    $page = visit('/character-builder');
    
    // Navigate through class selection
    $page->waitForText('Warrior', 10)
         ->click('Warrior');
    
    // Verify Warrior's Indomitable Spirit features
    $page->assertSee('Indomitable Spirit')
         ->assertSee('resist mental effects')
         ->assertSee('unbreakable will')
         ->assertSee('mental fortitude');
    
    // Continue to subclass
    $page->click('Next')
         ->waitForText('Call of the Brave', 5)
         ->click('Call of the Brave');
    
    // Verify Call of the Brave amplifies mental strength
    $page->assertSee('Legendary Courage')    // Fear immunity and spreading courage
         ->assertSee('Commander\'s Will')     // Ultimate mental leadership
         ->assertSee('Inspiring Presence');   // Boost ally morale and determination
    
    // The synergy: Indomitable Spirit provides personal mental fortitude,
    // Call of the Brave spreads that mental strength to allies
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('warrior call of brave domain access verification', function () {
    // Test that Warrior class provides correct domain access (Blade + Bone)
    // and Call of the Brave doesn't modify domain access
    
    $page = visit('/character-builder');
    
    // Navigate to domain cards step
    $page->waitForText('Warrior', 10)
         ->click('Warrior')
         ->click('Next')
         ->waitForText('Call of the Brave', 5)
         ->click('Call of the Brave');
    
    // Skip to domain cards section
    for ($i = 0; $i < 7; $i++) { // Skip ancestry, community, traits, info, equipment, background, experiences
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Choose Domain Cards', 5);
    
    // Verify correct domains are available
    $page->assertSee('Available Domains: Blade, Bone')
         ->assertSee('Blade Domain')
         ->assertSee('Bone Domain');
    
    // Verify level 1 cards from both domains are available
    $page->assertPresent('[data-testid="domain-card-command-strike"]')
         ->assertPresent('[data-testid="domain-card-rally-troops"]');
    
    // Verify domain cards from other domains are NOT available
    $page->assertDontSee('Arcana Domain')
         ->assertDontSee('Splendor Domain')
         ->assertDontSee('Grace Domain')
         ->assertDontSee('Midnight Domain');
    
    // Verify can select exactly 2 cards
    $page->click('Command Strike')
         ->wait(0.5)
         ->assertSee('1/2 cards selected')
         ->click('Rally Troops')
         ->wait(0.5)
         ->assertSee('2/2 cards selected');
    
    // Verify cannot select a third card (buttons should be disabled)
    $remainingCards = $page->elements('[data-testid*="domain-card-"]:not([data-selected])');
    foreach ($remainingCards as $card) {
        expect($card->getAttribute('disabled'))->toBe('true');
    }
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('warrior call of brave presence scaling validation', function () {
    // Test that Call of the Brave's Presence-based abilities are properly explained
    // Leadership effectiveness and inspiration power scaling
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Warrior', 10)
         ->click('Warrior')
         ->click('Next')
         ->waitForText('Call of the Brave', 5)
         ->click('Call of the Brave');
    
    // Verify Presence scaling is clearly explained
    $page->assertSee('Inspiring Presence')
         ->assertSee('leadership effectiveness scales with Presence')
         ->assertSee('charisma affects inspiration power');
    
    $page->assertSee('Battle Cry')
         ->assertSee('rally effectiveness based on Presence')
         ->assertSee('commanding voice');
    
    // Verify spellcast trait connection
    $page->assertSee('Spellcast Trait: Presence')
         ->assertSee('Your leadership magic uses Presence');
    
    // Navigate to trait assignment to verify integration
    for ($i = 0; $i < 3; $i++) { // Skip ancestry, community
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Assign Your Traits', 5);
    
    // Verify Presence is highlighted as spellcast trait
    $page->assertSee('Presence (Spellcast)')
         ->assertSee('This trait affects your leadership and inspiration abilities');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('warrior call of brave spellcast trait validation', function () {
    // Test that Call of the Brave correctly assigns Presence as spellcast trait
    // (different from Call of the Slayer which might use different trait)
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Warrior', 10)
         ->click('Warrior')
         ->click('Next')
         ->waitForText('Call of the Brave', 5)
         ->click('Call of the Brave');
    
    // Verify spellcast trait is displayed
    $page->assertSee('Spellcast Trait: Presence')
         ->assertSee('Your leadership magic uses Presence')
         ->assertSee('inspiring and commanding');
    
    // Navigate to trait assignment to verify integration
    for ($i = 0; $i < 3; $i++) { // Skip ancestry, community
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Assign Your Traits', 5);
    
    // Verify Presence is highlighted as spellcast trait
    $page->assertSee('Presence (Spellcast)')
         ->assertSee('This is your primary leadership trait');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
