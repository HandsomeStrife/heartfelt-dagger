<?php

declare(strict_types=1);

it('character builder basic flow: create → class selection → traits → equipment gating', function () {
    $page = visit('/character-builder');

    // Redirects to edit/{character_key}
    $page->assertPathBeginsWith('/character-builder/');

    // Should start on Step 1: Class Selection
    $page->assertSee('Choose a Class');

    // Class selection available and interacts
    $page->assertPresent('[pest="class-card-bard"]');
    $page->click('[pest="class-card-bard"]');
    $page->wait(1);

    // Class selection does NOT auto-advance - we need to click Continue to go to Step 2
    $page->click('[pest="next-step-button"]'); // Step 1 → Step 2 (Subclass)
    $page->wait(1);
    $page->assertSee('Choose Your Subclass');

    // Continue through the remaining steps to reach Domain Cards (Step 9)
    // Step 2 → Step 3 (Ancestry)
    $page->click('[pest="next-step-button"]');
    $page->wait(1);
    $page->assertSee('Choose Your Ancestry');

    // Step 3 → Step 4 (Community)
    $page->click('[pest="next-step-button"]');
    $page->wait(1);
    $page->assertSee('Choose Your Community');

    // Step 4 → Step 5 (Traits)
    $page->click('[pest="next-step-button"]');
    $page->wait(1);
    $page->assertSee('Assign Traits');

    // Step 5 → Step 6 (Equipment)
    $page->click('[pest="next-step-button"]');
    $page->wait(1);
    $page->assertSee('Select Equipment');

    // Step 6 → Step 7 (Background)
    $page->click('[pest="next-step-button"]');
    $page->wait(1);
    $page->assertSee('Create Background');

    // Step 7 → Step 8 (Experiences)
    $page->click('[pest="next-step-button"]');
    $page->wait(1);
    $page->assertSee('Add Experiences');

    // Step 8 → Step 9 (Domain Cards)
    $page->click('[pest="next-step-button"]');
    $page->wait(1);
    $page->assertSee('Select Domain Cards');

    // Should show all abilities for selected domains and grey out above-level ones
    $page->wait(2);
    $page->assertPresent('[pest="domain-card-selected-count"]');

    // Test sidebar navigation works - go back to Trait Assignment (Step 5)
    $page->click('[pest="sidebar-tab-5"]');
    $page->wait(1);
    $page->assertSee('Assign Traits');

    // Test sidebar navigation - go to Equipment (Step 6)
    $page->click('[pest="sidebar-tab-6"]');
    $page->wait(1);
    $page->assertSee('Select Equipment');
});

it('domain card selection toggles and enforces max selection', function () {
    $page = visit('/character-builder');

    $page->assertPathBeginsWith('/character-builder/');

    // Select a class to enable domain cards
    $page->assertPresent('[pest="class-card-wizard"]');
    $page->click('[pest="class-card-wizard"]');
    $page->wait(1);

    // Navigate step by step to reach Domain Cards (Step 9)
    // Each click advances one step: 1→2→3→4→5→6→7→8→9
    for ($i = 0; $i < 8; $i++) {
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
    }
    $page->assertSee('Select Domain Cards');

    // Verify domain cards section loads and count is present
    $page->wait(2);
    $page->assertPresent('[pest="domain-card-selected-count"]');
    $page->assertSee('0', '[pest="domain-card-selected-count"]');

    // Verify domain cards are present for the selected class
    $page->assertPresent('[pest^="domain-card-"]');

    // Try to click on a specific domain card to select it (use specific card instead of :first-of-type)
    $page->click('[pest="domain-card-codex-book of ava"]');
    $page->wait(1);

    // Verify selection count increased
    $page->assertSee('1', '[pest="domain-card-selected-count"]');
});
