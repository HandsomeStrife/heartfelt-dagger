<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive Pest 4 browser test for Druid + Warden of Renewal character creation workflow
 * 
 * This test validates the complete character creation process through UI interactions:
 * - Step 1: Class selection (Druid)
 * - Step 2: Subclass selection (Warden of Renewal)
 * - Step 3: Ancestry selection  
 * - Step 4: Community selection
 * - Step 5: Trait assignment (validates exact -1,0,0,+1,+1,+2 distribution)
 * - Step 6: Character stats verification
 * - Step 7: Equipment selection
 * - Step 8: Background questions
 * - Step 9: Experiences
 * - Step 10: Domain cards (Sage + Arcana access)
 * - Step 11: Connections
 * 
 * Druid + Warden of Renewal specific validations:
 * - Druid base stats (Hit Points: 5, Evasion: 9, Stress: 6)
 * - Warden of Renewal uses Instinct as spellcast trait
 * - Beastform transformation mechanics with Wildtouch
 * - Clarity of Nature: area stress clearing based on Instinct
 * - Regeneration: Hope-based Hit Point clearing (3 Hope for 1d4 HP)
 * - Regenerative Reach: range extension for healing
 * - Warden's Protection: mass healing (2 Hope for 2 HP on 1d4 allies)
 * - Defender: Beastform-based ally protection with stress cost
 * - Evolution Hope feature integration with healing focus
 */

test('druid warden of renewal complete character creation workflow', function () {
    // Start at character builder main page
    $page = visit('/character-builder');
    
    // Verify initial page load
    $page->assertSee('Character Builder')
         ->waitForText('Druid', 10);
    
    // Step 1: Select Druid class
    $page->click('Druid')
         ->waitForText('Druid', 5)
         ->assertSee('Sage')
         ->assertSee('Arcana')
         ->assertSee('Starting Evasion: 9')
         ->assertSee('Starting Hit Points: 5')
         ->assertSee('Beastform')
         ->assertSee('Wildtouch')
         ->assertSee('Evolution')
         ->assertSee('nature magic and transformation');
    
    // Navigate to subclass selection
    $page->click('Next')
         ->waitForText('Warden of Renewal', 5);
    
    // Step 2: Select Warden of Renewal subclass
    $page->click('Warden of Renewal')
         ->waitForText('Warden of Renewal', 5)
         ->assertSee('Clarity of Nature')
         ->assertSee('once per long rest')
         ->assertSee('space of natural serenity within Close range')
         ->assertSee('clear Stress equal to your Instinct')
         ->assertSee('distributed as you choose')
         ->assertSee('Regeneration')
         ->assertSee('Touch a creature and spend 3 Hope')
         ->assertSee('clear 1d4 Hit Points')
         ->assertSee('Regenerative Reach')
         ->assertSee('target creatures within Very Close range')
         ->assertSee('Warden\'s Protection')
         ->assertSee('spend 2 Hope to clear 2 Hit Points')
         ->assertSee('1d4 allies within Close range')
         ->assertSee('Defender')
         ->assertSee('in Beastform and an ally within Close range')
         ->assertSee('marks 2 or more Hit Points')
         ->assertSee('mark a Stress to reduce')
         ->assertSee('Spellcast Trait: Instinct');
    
    // Navigate to ancestry selection
    $page->click('Next')
         ->waitForText('Choose Your Ancestry', 5);
    
    // Step 3: Select Elf ancestry (connection to nature for druid)
    $page->click('Elf')
         ->waitForText('Elf', 5)
         ->assertSee('Graceful beings')
         ->assertSee('keen senses');
    
    // Navigate to community selection
    $page->click('Next')
         ->waitForText('Choose Your Community', 5);
    
    // Step 4: Select Wildborne community (perfect for nature druid)
    $page->click('Wildborne')
         ->waitForText('Wildborne', 5)
         ->assertSee('Wilderness-dwelling')
         ->assertSee('survival-focused');
    
    // Navigate to trait assignment
    $page->click('Next')
         ->waitForText('Assign Your Traits', 5);
    
    // Step 5: Assign traits with exact DaggerHeart distribution (-1,0,0,+1,+1,+2)
    // Focus on Instinct (spellcast trait) and nature-based capabilities
    $page->click('Agility: +1')   // +1 (mobility in nature)
         ->wait(0.5)
         ->click('Strength: +1')  // +1 (physical capability)
         ->wait(0.5)
         ->click('Finesse: 0')    // 0
         ->wait(0.5)
         ->click('Instinct: +2')  // +2 (primary spellcast trait)
         ->wait(0.5)
         ->click('Presence: 0')   // 0
         ->wait(0.5)
         ->click('Knowledge: -1') // -1 (less academic focus)
         ->waitForText('Trait assignment complete!', 3);
    
    // Navigate to character info section
    $page->click('Next')
         ->waitForText('Character Information', 5);
    
    // Step 6: Verify character stats are calculated correctly
    // Druid base: 5 HP, 9 Evasion, 6 Stress
    // Agility +1 affects evasion
    $page->assertSee('Hit Points: 5')  // Druid base
         ->assertSee('Evasion: 10')    // 9 base + 1 agility
         ->assertSee('Stress: 6')      // Druid base
         ->assertSee('Spellcast Trait: Instinct (+2)');
    
    // Fill in character name
    $page->type('#character-name', 'Sylvana Moonleaf')
         ->type('#character-pronouns', 'she/her')
         ->waitForText('Character details saved', 3);
    
    // Navigate to equipment selection
    $page->click('Next')
         ->waitForText('Choose Your Equipment', 5);
    
    // Step 7: Select appropriate equipment for healing druid
    $page->click('Quarterstaff')      // Natural weapon
         ->wait(0.5)
         ->click('Leather Armor')     // Light armor for mobility
         ->wait(0.5)
         ->click('Healer\'s Kit')     // Healing supplies
         ->waitForText('Equipment selection complete!', 3);
    
    // Navigate to background questions
    $page->click('Next')
         ->waitForText('Background Questions', 5);
    
    // Step 8: Answer druid-specific background questions
    $page->type('#background-0', 'I was raised by a grove of ancient spirits who taught me that all life is interconnected and must be protected.')
         ->wait(0.5)
         ->type('#background-1', 'A blight is spreading through the forest, and I must find its source before it corrupts the entire woodland realm.')
         ->wait(0.5)
         ->type('#background-2', 'I fear that my connection to nature makes me too distant from civilization to truly help people in cities.')
         ->waitForText('Background complete!', 3);
    
    // Navigate to experiences
    $page->click('Next')
         ->waitForText('Choose Your Experiences', 5);
    
    // Step 9: Create two experiences
    $page->type('#experience-0', 'Herbal Medicine')
         ->wait(0.5)
         ->type('#experience-1', 'Animal Communication')
         ->waitForText('Experiences complete!', 3);
    
    // Navigate to domain cards
    $page->click('Next')
         ->waitForText('Choose Domain Cards', 5);
    
    // Step 10: Select domain cards from Sage and Arcana domains
    $page->assertSee('Available Domains: Sage, Arcana')
         ->click('Heal')              // Level 1 Sage card
         ->wait(0.5)
         ->click('Restore')           // Level 1 Arcana card
         ->waitForText('Domain cards selected!', 3)
         ->assertSee('2/2 cards selected');
    
    // Navigate to connections
    $page->click('Next')
         ->waitForText('Character Connections', 5);
    
    // Step 11: Answer connection questions
    $page->type('#connection-0', 'I used my healing magic to save your life when you were poisoned, creating a bond deeper than friendship.')
         ->wait(0.5)
         ->type('#connection-1', 'You helped me save an entire grove from developers, proving your respect for the natural world.')
         ->wait(0.5)
         ->type('#connection-2', 'We both know that I can speak with the spirits of the forest, but you\'ve promised to keep this gift secret.')
         ->waitForText('Connections complete!', 3);
    
    // Navigate to final character sheet
    $page->click('Finish Character')
         ->waitForText('Sylvana Moonleaf', 5);
    
    // Final validation: Verify complete character sheet
    $page->assertSee('Sylvana Moonleaf')
         ->assertSee('Druid')
         ->assertSee('Warden of Renewal')
         ->assertSee('Elf')
         ->assertSee('Wildborne')
         ->assertSee('Hit Points: 5')
         ->assertSee('Evasion: 10')
         ->assertSee('Stress: 6')
         ->assertSee('Agility: +1')
         ->assertSee('Strength: +1')
         ->assertSee('Finesse: 0')
         ->assertSee('Instinct: +2')
         ->assertSee('Presence: 0')
         ->assertSee('Knowledge: -1')
         ->assertSee('Beastform')
         ->assertSee('Wildtouch')
         ->assertSee('Clarity of Nature')
         ->assertSee('Regeneration')
         ->assertSee('Regenerative Reach')
         ->assertSee('Warden\'s Protection')
         ->assertSee('Defender')
         ->assertSee('Heal')
         ->assertSee('Restore')
         ->assertSee('Quarterstaff')
         ->assertSee('Leather Armor')
         ->assertSee('Herbal Medicine')
         ->assertSee('Animal Communication');
    
    // Verify navigation to character viewer works
    $page->click('View Character')
         ->waitForText('Sylvana Moonleaf', 5)
         ->assertSee('Druid • Warden of Renewal')
         ->assertSee('Elf Wildborne');
    
    // Extract character key from URL to verify database persistence
    $currentUrl = $page->driver->getCurrentURL();
    preg_match('/character-viewer\/([A-Z0-9]{8,})/', $currentUrl, $matches);
    expect($matches)->toHaveCount(2);
    
    $characterKey = $matches[1];
    
    // Verify character was saved correctly in database
    $character = Character::where('character_key', $characterKey)->first();
    expect($character)->not()->toBeNull()
        ->class->toBe('druid')
        ->subclass->toBe('warden of renewal')
        ->ancestry->toBe('elf')
        ->community->toBe('wildborne')
        ->name->toBe('Sylvana Moonleaf')
        ->pronouns->toBe('she/her');
    
    // Verify traits were saved with correct distribution
    $traits = $character->traits()->pluck('trait_value', 'trait_name')->toArray();
    expect($traits)->toBe([
        'agility' => 1,
        'strength' => 1,
        'finesse' => 0,
        'instinct' => 2,
        'presence' => 0,
        'knowledge' => -1,
    ]);
    
    // Verify character stats calculation
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(5);  // Druid base
    expect($stats->evasion)->toBe(10);    // 9 base + 1 agility
    expect($stats->stress)->toBe(6);      // Druid base
    
    // Verify spellcast trait is correct
    expect($character->getSpellcastTrait())->toBe('instinct');
    expect($character->getSpellcastTraitValue())->toBe(2);
    
    // Verify domain cards were saved
    $domainCards = $character->domainCards()->pluck('card_name')->toArray();
    expect($domainCards)->toContain('Heal')
                       ->toContain('Restore');
    
    // Verify experiences were saved
    $experiences = $character->experiences()->pluck('experience_name')->toArray();
    expect($experiences)->toContain('Herbal Medicine')
                       ->toContain('Animal Communication');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('druid warden of renewal healing mechanics validation', function () {
    // Test that Warden of Renewal healing mechanics are properly displayed
    // and Hope/Stress costs are clearly explained
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Druid', 10)
         ->click('Druid')
         ->click('Next')
         ->waitForText('Warden of Renewal', 5)
         ->click('Warden of Renewal');
    
    // Verify healing progression
    $page->assertSee('Clarity of Nature')
         ->assertSee('once per long rest')
         ->assertSee('create a space of natural serenity')
         ->assertSee('clear Stress equal to your Instinct')
         ->assertSee('distributed as you choose between you and your allies');
    
    $page->assertSee('Regeneration')
         ->assertSee('Touch a creature and spend 3 Hope')
         ->assertSee('That creature clears 1d4 Hit Points');
    
    $page->assertSee('Regenerative Reach')
         ->assertSee('target creatures within Very Close range')
         ->assertSee('with your "Regeneration" feature');
    
    $page->assertSee('Warden\'s Protection')
         ->assertSee('once per long rest')
         ->assertSee('spend 2 Hope to clear 2 Hit Points')
         ->assertSee('on 1d4 allies within Close range');
    
    $page->assertSee('Defender')
         ->assertSee('in Beastform and an ally within Close range')
         ->assertSee('marks 2 or more Hit Points')
         ->assertSee('mark a Stress to reduce the number')
         ->assertSee('Hit Points they mark by 1');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('druid warden of renewal beastform integration validation', function () {
    // Test that Warden of Renewal features integrate with Druid's Beastform
    
    $page = visit('/character-builder');
    
    // Navigate through class selection
    $page->waitForText('Druid', 10)
         ->click('Druid');
    
    // Verify Druid's Beastform features are shown
    $page->assertSee('Beastform')
         ->assertSee('Mark a Stress to magically transform')
         ->assertSee('creature of your tier or lower')
         ->assertSee('gain the Beastform\'s features')
         ->assertSee('add their Evasion bonus to your Evasion')
         ->assertSee('Evolution')
         ->assertSee('Spend 3 Hope to transform into a Beastform')
         ->assertSee('without marking a Stress')
         ->assertSee('choose one trait to raise by +1');
    
    // Continue to subclass
    $page->click('Next')
         ->waitForText('Warden of Renewal', 5)
         ->click('Warden of Renewal');
    
    // Verify Warden of Renewal enhances Beastform with healing focus
    $page->assertSee('Defender')
         ->assertSee('Your animal transformation embodies a healing guardian spirit')
         ->assertSee('while you\'re in Beastform')
         ->assertSee('an ally within Close range marks 2 or more Hit Points')
         ->assertSee('mark a Stress to reduce the number');
    
    // The synergy: Beastform provides enhanced abilities and positioning,
    // Defender allows protection of allies while transformed
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('druid warden of renewal hope economy validation', function () {
    // Test that Warden of Renewal's Hope costs are properly balanced
    // Regeneration: 3 Hope for 1d4 HP, Warden's Protection: 2 Hope for 2 HP on 1d4 allies
    
    $page = visit('/character-builder');
    
    // Navigate through class selection
    $page->waitForText('Druid', 10)
         ->click('Druid');
    
    // Verify Druid's Evolution Hope feature
    $page->assertSee('Evolution')
         ->assertSee('Spend 3 Hope to transform into a Beastform')
         ->assertSee('without marking a Stress');
    
    // Continue to subclass
    $page->click('Next')
         ->waitForText('Warden of Renewal', 5)
         ->click('Warden of Renewal');
    
    // Verify Hope-based healing costs
    $page->assertSee('Regeneration')
         ->assertSee('spend 3 Hope')
         ->assertSee('clear 1d4 Hit Points');
    
    $page->assertSee('Warden\'s Protection')
         ->assertSee('spend 2 Hope')
         ->assertSee('clear 2 Hit Points on 1d4 allies');
    
    // Hope economy analysis:
    // - Evolution: 3 Hope for enhanced transformation
    // - Regeneration: 3 Hope for single-target healing (1d4 HP)
    // - Warden's Protection: 2 Hope for multi-target healing (2 HP each)
    // This encourages tactical Hope management between transformation and healing
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('druid warden of renewal domain access verification', function () {
    // Test that Druid class provides correct domain access (Sage + Arcana)
    // and Warden of Renewal doesn't modify domain access
    
    $page = visit('/character-builder');
    
    // Navigate to domain cards step
    $page->waitForText('Druid', 10)
         ->click('Druid')
         ->click('Next')
         ->waitForText('Warden of Renewal', 5)
         ->click('Warden of Renewal');
    
    // Skip to domain cards section
    for ($i = 0; $i < 7; $i++) { // Skip ancestry, community, traits, info, equipment, background, experiences
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Choose Domain Cards', 5);
    
    // Verify correct domains are available
    $page->assertSee('Available Domains: Sage, Arcana')
         ->assertSee('Sage Domain')
         ->assertSee('Arcana Domain');
    
    // Verify level 1 cards from both domains are available
    $page->assertPresent('[data-testid="domain-card-heal"]')
         ->assertPresent('[data-testid="domain-card-restore"]');
    
    // Verify domain cards from other domains are NOT available
    $page->assertDontSee('Valor Domain')
         ->assertDontSee('Blade Domain')
         ->assertDontSee('Grace Domain')
         ->assertDontSee('Midnight Domain');
    
    // Verify can select exactly 2 cards
    $page->click('Heal')
         ->wait(0.5)
         ->assertSee('1/2 cards selected')
         ->click('Restore')
         ->wait(0.5)
         ->assertSee('2/2 cards selected');
    
    // Verify cannot select a third card (buttons should be disabled)
    $remainingCards = $page->elements('[data-testid*="domain-card-"]:not([data-selected])');
    foreach ($remainingCards as $card) {
        expect($card->getAttribute('disabled'))->toBe('true');
    }
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('druid warden of renewal instinct scaling validation', function () {
    // Test that Warden of Renewal's Instinct-based scaling is properly explained
    // Clarity of Nature clears Stress equal to Instinct
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Druid', 10)
         ->click('Druid')
         ->click('Next')
         ->waitForText('Warden of Renewal', 5)
         ->click('Warden of Renewal');
    
    // Verify Instinct scaling is clearly explained
    $page->assertSee('Clarity of Nature')
         ->assertSee('clear Stress equal to your Instinct')
         ->assertSee('distributed as you choose');
    
    // Verify spellcast trait connection
    $page->assertSee('Spellcast Trait: Instinct')
         ->assertSee('Your nature magic uses Instinct');
    
    // Navigate to trait assignment to verify integration
    for ($i = 0; $i < 3; $i++) { // Skip ancestry, community
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Assign Your Traits', 5);
    
    // Verify Instinct is highlighted as spellcast trait
    $page->assertSee('Instinct (Spellcast)')
         ->assertSee('This trait also affects Clarity of Nature healing');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
