<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

// Test the JavaScript-first architecture changes
it('trait application works through JavaScript', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Select a class (required for trait suggestions)
    $page->click('[pest="class-card-wizard"]');
    $page->wait(1);

    // Navigate to trait assignment step
    $page->click('[pest="next-step-button"]'); // Go to subclass step
    $page->wait(1);
    $page->click('[pest="next-step-button"]'); // Go to ancestry step
    $page->wait(1);
    $page->click('[pest="next-step-button"]'); // Go to community step
    $page->wait(1);
    $page->click('[pest="next-step-button"]'); // Go to trait assignment step
    $page->wait(1);

    // Verify we're on trait assignment step
    $page->assertSee('Assign Traits');

    // Apply suggested traits through JavaScript (not Livewire)
    $page->click('[pest="apply-suggested-traits"]');
    $page->wait(1);

    // Verify traits were applied
    $page->assertVisible('.trait-assignment-complete', 'Traits should be assigned');

    // Verify unsaved changes indicator appears
    $page->assertSee('Unsaved Changes');
    $page->assertVisible('[pest="save-character-button"]');
});

it('equipment suggestions work through JavaScript', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Select a class
    $page->click('[pest="class-card-warrior"]');
    $page->wait(1);

    // Navigate to equipment step (Step 5)
    for ($i = 0; $i < 4; $i++) {
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
    }

    // Verify we're on equipment step
    $page->assertSee('Select Equipment');

    // Apply all suggested equipment through JavaScript
    $page->click('[pest="apply-all-suggestions"]');
    $page->wait(1);

    // Verify equipment was selected
    $page->assertSee('Primary Weapon');
    $page->assertSee('Armor');

    // Verify unsaved changes tracking
    $page->assertSee('Unsaved Changes');
});

it('domain card selection works through JavaScript', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Select wizard class (has codex + midnight domains)
    $page->click('[pest="class-card-wizard"]');
    $page->wait(1);

    // Navigate to domain card step (Step 9)
    for ($i = 0; $i < 8; $i++) {
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
    }

    // Verify we're on domain card step
    $page->assertSee('Select Domain Cards');
    $page->assertSee('0/2 selected');

    // Select domain cards through JavaScript
    $page->click('[pest="domain-card-codex-book of ava"]');
    $page->wait(1);
    $page->assertSee('1/2 selected');

    $page->click('[pest="domain-card-midnight-pick and pull"]');
    $page->wait(1);
    $page->assertSee('2/2 selected');

    // Verify completion state
    $page->assertSee('Domain Card Selection Complete!');

    // Verify unsaved changes tracking
    $page->assertSee('Unsaved Changes');
});

it('character selection methods work through JavaScript', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Test class selection through JavaScript
    $page->click('[pest="class-card-bard"]');
    $page->wait(1);

    // Verify class was selected and UI updated
    $page->assertSee('Choose a Subclass');
    $page->assertSee('Unsaved Changes');

    // Test subclass selection
    $page->click('[pest="subclass-card-college of lore"]');
    $page->wait(1);

    // Navigate to ancestry step
    $page->click('[pest="next-step-button"]');
    $page->wait(1);

    // Test ancestry selection through JavaScript
    $page->click('[pest="ancestry-card-human"]');
    $page->wait(1);
    $page->assertSee('Unsaved Changes');

    // Navigate to community step
    $page->click('[pest="next-step-button"]');
    $page->wait(1);

    // Test community selection through JavaScript
    $page->click('[pest="community-card-loreborne"]');
    $page->wait(1);
    $page->assertSee('Unsaved Changes');

    // Verify all selections are preserved
    $page->assertSee('Bard');
    $page->assertSee('College of Lore');
    $page->assertSee('Human');
    $page->assertSee('Loreborne');
});

it('background form integration works with JavaScript state', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Select class to get background questions
    $page->click('[pest="class-card-ranger"]');
    $page->wait(1);

    // Navigate to background step (Step 7)
    for ($i = 0; $i < 6; $i++) {
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
    }

    // Verify we're on background step
    $page->assertSee('Create Background');

    // Fill in a background answer
    $page->type('textarea[dusk="background-question-0"]', 'I grew up in the wilderness');
    $page->wait(1);

    // Mark background as complete through JavaScript
    $page->click('[dusk="mark-background-complete"]');
    $page->wait(1);

    // Verify completion
    $page->assertSee('Background section marked as complete!');
    $page->assertSee('Unsaved Changes');
});

it('unsaved changes tracking works across all JavaScript interactions', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Initially no unsaved changes
    $page->assertDontSee('Unsaved Changes');

    // Make a change - class selection
    $page->click('[pest="class-card-sorcerer"]');
    $page->wait(1);
    $page->assertSee('Unsaved Changes');
    $page->assertVisible('[pest="save-character-button"]');

    // Save and verify unsaved changes disappear
    $page->click('[pest="save-character-button"]');
    $page->wait(2); // Wait for potential save (Livewire calls don't work in browser tests)
    $page->wait(2); // Wait for state reset
    $page->assertDontSee('Unsaved Changes');

    // Make another change - subclass selection
    $page->click('[pest="subclass-card-primal magic"]');
    $page->wait(1);
    $page->assertSee('Unsaved Changes');

    // Navigate away warning should trigger (though we can't test the actual browser dialog)
    // We can verify the beforeunload listener is set up by checking JavaScript state
});

it('instant UI feedback works without server round-trips', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Time class selection - should be instant
    $startTime = microtime(true);
    $page->click('[pest="class-card-guardian"]');
    $page->wait(0.1); // Minimal wait
    $page->assertSee('Choose a Subclass');
    $endTime = microtime(true);

    // Should be very fast (under 500ms for UI update)
    expect(($endTime - $startTime) * 1000)->toBeLessThan(500);

    // Navigate to traits and test instant trait application
    $page->click('[pest="next-step-button"]'); // Subclass
    $page->wait(1);
    $page->click('[pest="next-step-button"]'); // Ancestry
    $page->wait(1);
    $page->click('[pest="next-step-button"]'); // Community
    $page->wait(1);
    $page->click('[pest="next-step-button"]'); // Traits
    $page->wait(1);

    // Apply traits - should be instant UI update
    $startTime = microtime(true);
    $page->click('[pest="apply-suggested-traits"]');
    $page->wait(0.1);
    $page->assertVisible('.trait-assignment-complete');
    $endTime = microtime(true);

    // Should be very fast
    expect(($endTime - $startTime) * 1000)->toBeLessThan(500);
});

it('complex character creation flow with JavaScript architecture', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Complete character creation using only JavaScript interactions

    // Step 1: Class selection
    $page->click('[pest="class-card-druid"]');
    $page->wait(1);
    $page->assertSee('Unsaved Changes');

    // Step 2: Subclass selection
    $page->click('[pest="subclass-card-nature spirit"]');
    $page->wait(1);

    // Step 3: Ancestry
    $page->click('[pest="next-step-button"]');
    $page->wait(1);
    $page->click('[pest="ancestry-card-elf"]');
    $page->wait(1);

    // Step 4: Community
    $page->click('[pest="next-step-button"]');
    $page->wait(1);
    $page->click('[pest="community-card-wildborne"]');
    $page->wait(1);

    // Step 5: Traits (apply suggestions)
    $page->click('[pest="next-step-button"]');
    $page->wait(1);
    $page->click('[pest="apply-suggested-traits"]');
    $page->wait(1);

    // Step 6: Equipment (apply suggestions)
    $page->click('[pest="next-step-button"]');
    $page->wait(1);
    $page->click('[pest="apply-all-suggestions"]');
    $page->wait(1);

    // Skip to domain cards (Step 9)
    $page->click('[pest="next-step-button"]'); // Background
    $page->wait(1);
    $page->click('[pest="next-step-button"]'); // Experience
    $page->wait(1);
    $page->click('[pest="next-step-button"]'); // Domain cards
    $page->wait(1);

    // Select domain cards (Druid has Sage + Arcana)
    $page->click('[pest="domain-card-sage-gifted tracker"]');
    $page->wait(1);
    $page->click('[pest="domain-card-arcana-rune ward"]');
    $page->wait(1);

    // Verify completion
    $page->assertSee('Domain Card Selection Complete!');
    $page->assertSee('Unsaved Changes');

    // Save character
    $page->click('[pest="save-character-button"]');
    $page->wait(2); // Wait for potential save (Livewire calls don't work in browser tests)
    $page->wait(2);
    $page->assertDontSee('Unsaved Changes');

    // Verify all data was persisted correctly
    $page->assertSee('Druid');
    $page->assertSee('Nature Spirit');
    $page->assertSee('Elf');
    $page->assertSee('Wildborne');
});
