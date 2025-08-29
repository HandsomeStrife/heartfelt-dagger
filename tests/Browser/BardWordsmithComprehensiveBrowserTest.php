<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

/**
 * Comprehensive Pest 4 browser test for Bard + Wordsmith character creation workflow
 * 
 * This test validates the complete character creation process through UI interactions:
 * - Step 1: Class selection (Bard)
 * - Step 2: Subclass selection (Wordsmith)
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
 * Bard + Wordsmith specific validations:
 * - Bard base stats (Hit Points: 5, Evasion: 10, Stress: 6)
 * - Wordsmith uses Presence as spellcast trait
 * - Written Magic: spell scroll creation and empowerment
 * - Living Lexicon: language-based knowledge and communication
 * - Textual Mastery: enhanced scroll usage and written spell effects
 * - Scholarly Pursuit: research and academic enhancement abilities
 * - Story Weaver: narrative-based reality alteration
 * - Counter Spelling: scroll-based magical interruption
 * - Rally class feature integration with scholarly magic
 */

test('bard wordsmith complete character creation workflow', function () {
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
         ->assertSee('Social Graces')
         ->assertSee('musical magic and performance');
    
    // Navigate to subclass selection
    $page->click('Next')
         ->waitForText('Wordsmith', 5);
    
    // Step 2: Select Wordsmith subclass
    $page->click('Wordsmith')
         ->waitForText('Wordsmith', 5)
         ->assertSee('Written Magic')
         ->assertSee('create spell scrolls')
         ->assertSee('spend 2 Hope to imbue a piece of paper')
         ->assertSee('domain card from your hand')
         ->assertSee('becomes a spell scroll')
         ->assertSee('Living Lexicon')
         ->assertSee('communicate with any intelligent creature')
         ->assertSee('understand any written language')
         ->assertSee('Textual Mastery')
         ->assertSee('cast from a scroll')
         ->assertSee('reduce the Recall Cost by 1')
         ->assertSee('Scholarly Pursuit')
         ->assertSee('spend time researching')
         ->assertSee('gain temporary knowledge')
         ->assertSee('Story Weaver')
         ->assertSee('rewrite minor aspects of reality')
         ->assertSee('change small details')
         ->assertSee('Counter Spelling')
         ->assertSee('interrupt a spell being cast')
         ->assertSee('scroll of the same domain')
         ->assertSee('Spellcast Trait: Presence');
    
    // Navigate to ancestry selection
    $page->click('Next')
         ->waitForText('Choose Your Ancestry', 5);
    
    // Step 3: Select Human ancestry (scholarly versatility)
    $page->click('Human')
         ->waitForText('Human', 5)
         ->assertSee('Versatile and adaptable')
         ->assertSee('+2 Stress');
    
    // Navigate to community selection
    $page->click('Next')
         ->waitForText('Choose Your Community', 5);
    
    // Step 4: Select Loreborne community (academic focus for wordsmith)
    $page->click('Loreborne')
         ->waitForText('Loreborne', 5)
         ->assertSee('Academic')
         ->assertSee('scholarly')
         ->assertSee('knowledge-seeking');
    
    // Navigate to trait assignment
    $page->click('Next')
         ->waitForText('Assign Your Traits', 5);
    
    // Step 5: Assign traits with exact DaggerHeart distribution (-1,0,0,+1,+1,+2)
    // Focus on Presence (spellcast trait) and scholarly capabilities
    $page->click('Agility: 0')     // 0
         ->wait(0.5)
         ->click('Strength: -1')   // -1 (less physical focus)
         ->wait(0.5)
         ->click('Finesse: +1')    // +1 (precise writing)
         ->wait(0.5)
         ->click('Instinct: 0')    // 0
         ->wait(0.5)
         ->click('Presence: +2')   // +2 (primary spellcast trait)
         ->wait(0.5)
         ->click('Knowledge: +1')  // +1 (scholarly pursuits)
         ->waitForText('Trait assignment complete!', 3);
    
    // Navigate to character info section
    $page->click('Next')
         ->waitForText('Character Information', 5);
    
    // Step 6: Verify character stats are calculated correctly
    // Bard base: 5 HP, 10 Evasion, 6 Stress
    // Human ancestry: +2 Stress
    $page->assertSee('Hit Points: 5')  // Bard base
         ->assertSee('Evasion: 10')    // Bard base (no agility bonus)
         ->assertSee('Stress: 8')      // 6 base + 2 human
         ->assertSee('Spellcast Trait: Presence (+2)');
    
    // Fill in character name
    $page->type('#character-name', 'Quill Storywright')
         ->type('#character-pronouns', 'they/them')
         ->waitForText('Character details saved', 3);
    
    // Navigate to equipment selection
    $page->click('Next')
         ->waitForText('Choose Your Equipment', 5);
    
    // Step 7: Select appropriate equipment for scholarly bard
    $page->click('Scholar\'s Tools')  // Writing implements
         ->wait(0.5)
         ->click('Light Armor')       // Mobility for scholar
         ->wait(0.5)
         ->click('Spell Components')  // Magical focus
         ->waitForText('Equipment selection complete!', 3);
    
    // Navigate to background questions
    $page->click('Next')
         ->waitForText('Background Questions', 5);
    
    // Step 8: Answer bard-specific background questions
    $page->type('#background-0', 'I discovered an ancient library where the books literally wrote themselves, and I learned to channel that living knowledge.')
         ->wait(0.5)
         ->type('#background-1', 'A powerful wizard\'s spell scroll was torn in half, and I must find the missing piece before the incomplete magic destroys a city.')
         ->wait(0.5)
         ->type('#background-2', 'I fear that my written magic is slowly erasing my own memories, replacing them with the knowledge of others.')
         ->waitForText('Background complete!', 3);
    
    // Navigate to experiences
    $page->click('Next')
         ->waitForText('Choose Your Experiences', 5);
    
    // Step 9: Create two experiences
    $page->type('#experience-0', 'Ancient Languages')
         ->wait(0.5)
         ->type('#experience-1', 'Scroll Crafting')
         ->waitForText('Experiences complete!', 3);
    
    // Navigate to domain cards
    $page->click('Next')
         ->waitForText('Choose Domain Cards', 5);
    
    // Step 10: Select domain cards from Grace and Codex domains
    $page->assertSee('Available Domains: Grace, Codex')
         ->click('Identify')          // Level 1 Codex card
         ->wait(0.5)
         ->click('Charm Person')      // Level 1 Grace card
         ->waitForText('Domain cards selected!', 3)
         ->assertSee('2/2 cards selected');
    
    // Navigate to connections
    $page->click('Next')
         ->waitForText('Character Connections', 5);
    
    // Step 11: Answer connection questions
    $page->type('#connection-0', 'I wrote your true name in my journal, and now our fates are magically intertwined until the book is destroyed.')
         ->wait(0.5)
         ->type('#connection-1', 'You found me in a ruined library and helped me save the last remaining books from a fire.')
         ->wait(0.5)
         ->type('#connection-2', 'I accidentally read your private diary with my magic, and now I know a secret that could change everything.')
         ->waitForText('Connections complete!', 3);
    
    // Navigate to final character sheet
    $page->click('Finish Character')
         ->waitForText('Quill Storywright', 5);
    
    // Final validation: Verify complete character sheet
    $page->assertSee('Quill Storywright')
         ->assertSee('Bard')
         ->assertSee('Wordsmith')
         ->assertSee('Human')
         ->assertSee('Loreborne')
         ->assertSee('Hit Points: 5')
         ->assertSee('Evasion: 10')
         ->assertSee('Stress: 8')
         ->assertSee('Agility: 0')
         ->assertSee('Strength: -1')
         ->assertSee('Finesse: +1')
         ->assertSee('Instinct: 0')
         ->assertSee('Presence: +2')
         ->assertSee('Knowledge: +1')
         ->assertSee('Rally')
         ->assertSee('Social Graces')
         ->assertSee('Written Magic')
         ->assertSee('Living Lexicon')
         ->assertSee('Textual Mastery')
         ->assertSee('Scholarly Pursuit')
         ->assertSee('Story Weaver')
         ->assertSee('Counter Spelling')
         ->assertSee('Identify')
         ->assertSee('Charm Person')
         ->assertSee('Scholar\'s Tools')
         ->assertSee('Light Armor')
         ->assertSee('Ancient Languages')
         ->assertSee('Scroll Crafting');
    
    // Verify navigation to character viewer works
    $page->click('View Character')
         ->waitForText('Quill Storywright', 5)
         ->assertSee('Bard • Wordsmith')
         ->assertSee('Human Loreborne');
    
    // Extract character key from URL to verify database persistence
    $currentUrl = $page->driver->getCurrentURL();
    preg_match('/character-viewer\/([A-Z0-9]{8,})/', $currentUrl, $matches);
    expect($matches)->toHaveCount(2);
    
    $characterKey = $matches[1];
    
    // Verify character was saved correctly in database
    $character = Character::where('character_key', $characterKey)->first();
    expect($character)->not()->toBeNull()
        ->class->toBe('bard')
        ->subclass->toBe('wordsmith')
        ->ancestry->toBe('human')
        ->community->toBe('loreborne')
        ->name->toBe('Quill Storywright')
        ->pronouns->toBe('they/them');
    
    // Verify traits were saved with correct distribution
    $traits = $character->traits()->pluck('trait_value', 'trait_name')->toArray();
    expect($traits)->toBe([
        'agility' => 0,
        'strength' => -1,
        'finesse' => 1,
        'instinct' => 0,
        'presence' => 2,
        'knowledge' => 1,
    ]);
    
    // Verify character stats calculation
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBe(5);  // Bard base
    expect($stats->evasion)->toBe(10);    // Bard base (no agility bonus)
    expect($stats->stress)->toBe(8);      // 6 base + 2 human
    
    // Verify spellcast trait is correct
    expect($character->getSpellcastTrait())->toBe('presence');
    expect($character->getSpellcastTraitValue())->toBe(2);
    
    // Verify domain cards were saved
    $domainCards = $character->domainCards()->pluck('card_name')->toArray();
    expect($domainCards)->toContain('Identify')
                       ->toContain('Charm Person');
    
    // Verify experiences were saved
    $experiences = $character->experiences()->pluck('experience_name')->toArray();
    expect($experiences)->toContain('Ancient Languages')
                       ->toContain('Scroll Crafting');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('bard wordsmith written magic mechanics validation', function () {
    // Test that Wordsmith's Written Magic and scroll mechanics are properly displayed
    // and Hope costs for scroll creation are explained
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Bard', 10)
         ->click('Bard')
         ->click('Next')
         ->waitForText('Wordsmith', 5)
         ->click('Wordsmith');
    
    // Verify Written Magic mechanics
    $page->assertSee('Written Magic')
         ->assertSee('create spell scrolls')
         ->assertSee('spend 2 Hope to imbue a piece of paper')
         ->assertSee('domain card from your hand')
         ->assertSee('becomes a spell scroll')
         ->assertSee('that card\'s Recall Cost')
         ->assertSee('anyone can use the scroll');
    
    // Verify scroll enhancement
    $page->assertSee('Textual Mastery')
         ->assertSee('cast from a scroll')
         ->assertSee('reduce the Recall Cost by 1')
         ->assertSee('minimum of 0');
    
    // Verify magical interruption
    $page->assertSee('Counter Spelling')
         ->assertSee('interrupt a spell being cast')
         ->assertSee('scroll of the same domain')
         ->assertSee('cancel the spell');
    
    // Hope economy: 2 Hope to create scrolls, but scrolls can be used by anyone
    // and Wordsmith gets cost reduction when using them personally
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('bard wordsmith scholarly abilities validation', function () {
    // Test that Wordsmith's scholarly and research abilities are properly displayed
    
    $page = visit('/character-builder');
    
    // Navigate to subclass selection
    $page->waitForText('Bard', 10)
         ->click('Bard')
         ->click('Next')
         ->waitForText('Wordsmith', 5)
         ->click('Wordsmith');
    
    // Verify scholarly features
    $page->assertSee('Living Lexicon')
         ->assertSee('communicate with any intelligent creature')
         ->assertSee('understand any written language')
         ->assertSee('regardless of your actual knowledge');
    
    $page->assertSee('Scholarly Pursuit')
         ->assertSee('spend time researching')
         ->assertSee('gain temporary knowledge')
         ->assertSee('about a specific topic')
         ->assertSee('until your next long rest');
    
    $page->assertSee('Story Weaver')
         ->assertSee('rewrite minor aspects of reality')
         ->assertSee('change small details')
         ->assertSee('that don\'t significantly alter outcomes')
         ->assertSee('what someone was wearing')
         ->assertSee('color of their hair');
    
    // These abilities emphasize knowledge, communication, and minor reality editing
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('bard wordsmith rally integration validation', function () {
    // Test that Wordsmith features integrate with Bard's Rally social abilities
    
    $page = visit('/character-builder');
    
    // Navigate through class selection
    $page->waitForText('Bard', 10)
         ->click('Bard');
    
    // Verify Bard's Rally features are shown
    $page->assertSee('Rally')
         ->assertSee('Spend 3 Hope')
         ->assertSee('give an ally within Close range')
         ->assertSee('add +1 to all their rolls')
         ->assertSee('Social Graces')
         ->assertSee('advantage on social situations');
    
    // Continue to subclass
    $page->click('Next')
         ->waitForText('Wordsmith', 5)
         ->click('Wordsmith');
    
    // Verify Wordsmith complements Rally with communication and knowledge
    $page->assertSee('Living Lexicon')     // Universal communication
         ->assertSee('Story Weaver')       // Reality editing for social situations
         ->assertSee('Written Magic');     // Scroll creation for group utility
    
    // The synergy: Rally provides direct ally enhancement,
    // Wordsmith provides communication, knowledge, and utility scrolls
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('bard wordsmith domain access verification', function () {
    // Test that Bard class provides correct domain access (Grace + Codex)
    // and Wordsmith doesn't modify domain access
    
    $page = visit('/character-builder');
    
    // Navigate to domain cards step
    $page->waitForText('Bard', 10)
         ->click('Bard')
         ->click('Next')
         ->waitForText('Wordsmith', 5)
         ->click('Wordsmith');
    
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
    $page->assertPresent('[data-testid="domain-card-identify"]')
         ->assertPresent('[data-testid="domain-card-charm-person"]');
    
    // Verify domain cards from other domains are NOT available
    $page->assertDontSee('Arcana Domain')
         ->assertDontSee('Midnight Domain')
         ->assertDontSee('Valor Domain')
         ->assertDontSee('Blade Domain');
    
    // Verify can select exactly 2 cards
    $page->click('Identify')
         ->wait(0.5)
         ->assertSee('1/2 cards selected')
         ->click('Charm Person')
         ->wait(0.5)
         ->assertSee('2/2 cards selected');
    
    // Verify cannot select a third card (buttons should be disabled)
    $remainingCards = $page->elements('[data-testid*="domain-card-"]:not([data-selected])');
    foreach ($remainingCards as $card) {
        expect($card->getAttribute('disabled'))->toBe('true');
    }
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('bard wordsmith scroll creation hope economy validation', function () {
    // Test that Wordsmith's scroll creation Hope costs are properly balanced
    // Written Magic: 2 Hope to create scrolls that anyone can use
    
    $page = visit('/character-builder');
    
    // Navigate through class selection
    $page->waitForText('Bard', 10)
         ->click('Bard');
    
    // Verify Bard's Rally Hope cost
    $page->assertSee('Rally')
         ->assertSee('Spend 3 Hope')
         ->assertSee('give an ally within Close range');
    
    // Continue to subclass
    $page->click('Next')
         ->waitForText('Wordsmith', 5)
         ->click('Wordsmith');
    
    // Verify Written Magic Hope cost
    $page->assertSee('Written Magic')
         ->assertSee('spend 2 Hope to imbue a piece of paper')
         ->assertSee('becomes a spell scroll');
    
    // Hope economy analysis:
    // - Rally: 3 Hope for temporary ally enhancement
    // - Written Magic: 2 Hope for permanent scroll creation
    // This encourages creating scrolls as preparation vs immediate Rally effects
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('bard wordsmith spellcast trait validation', function () {
    // Test that Wordsmith correctly assigns Presence as spellcast trait
    // (same as Troubadour, emphasizing bard's social magic foundation)
    
    $page = visit('/character-builder');
    
    // Quick setup through UI
    $page->waitForText('Bard', 10)
         ->click('Bard')
         ->click('Next')
         ->waitForText('Wordsmith', 5)
         ->click('Wordsmith');
    
    // Verify spellcast trait is displayed
    $page->assertSee('Spellcast Trait: Presence')
         ->assertSee('Your written magic uses Presence');
    
    // Navigate to trait assignment to verify integration
    for ($i = 0; $i < 3; $i++) { // Skip ancestry, community
        $page->click('Next')->wait(1);
    }
    
    $page->waitForText('Assign Your Traits', 5);
    
    // Verify Presence is highlighted as spellcast trait
    $page->assertSee('Presence (Spellcast)')
         ->assertSee('This is your primary written magic trait');
    
})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
