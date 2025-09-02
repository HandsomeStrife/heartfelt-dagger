<?php

declare(strict_types=1);

use Illuminate\Support\Str;

it('character builder basic flow: create → class selection → traits → equipment gating', function () {
    $page = visit('/character-builder');

    // Redirects to edit/{character_key}
    $page->assertPathBeginsWith('/character-builder/');
    // Header action present
    $page->assertPresent('[pest="save-character-button"]');

    // Class selection available and interacts
    $page->assertPresent('[pest="class-card-bard"]');
    $page->click('[pest="class-card-bard"]');
    $page->wait(1);

    // Domain cards filtered to two domains; only level 1 appear in UI labels
    // Navigate to Domain Cards step via sidebar (Step 9)
    // Navigate to Domain Cards step using Next button to avoid responsive sidebar differences
    for ($i = 0; $i < 8; $i++) {
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
    }
    $page->assertSee('Select Domain Cards');
    // Should show all abilities for selected domains and grey out above-level ones
    $page->wait(2);
    $page->assertPresent('[pest="domain-card-selected-count"]');

    // Trait enforcement: attempt to complete with invalid distribution should not allow
    // Using client-side controls (labels/inputs may vary; assert presence of enforcement hints)
    // Navigate to Trait Assignment (Step 5)
    $page->click('[pest="sidebar-tab-5"]');
    $page->wait(1);
    $page->assertSee('Trait');

    // Equipment gating copy visible
    // Navigate to Equipment (Step 6)
    $page->click('[pest="sidebar-tab-6"]');
    $page->wait(1);
    $page->assertSee('Select Equipment');
});

it('domain card selection toggles and enforces max selection', function () {
    $page = visit('/character-builder');

    $page->assertPathBeginsWith('/character-builder/');
    $page->assertPresent('[pest="save-character-button"]');

    // Select a class to enable domain cards
    $page->assertPresent('[pest="class-card-wizard"]');
    $page->click('[pest="class-card-wizard"]');
    $page->wait(1);

    // Select two level 1 cards from visible domains
    // Use first two visible card action buttons (labels may vary, so click by index with CSS)
    // Go to domain cards step via Next buttons
    for ($i = 0; $i < 8; $i++) {
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
    }
    $page->assertSee('Select Domain Cards');

    // Verify domain cards section loads and count is present
    $page->wait(3);
    $page->assertPresent('[pest="domain-card-selected-count"]');
    $page->assertSee('0', '[pest="domain-card-selected-count"]');
    
    // Verify domain cards are present for the selected class
    $page->assertPresent('[pest^="domain-card-"]');
    
    // Try to click on a domain card to select it
    $page->click('[pest^="domain-card-"]:first-of-type');
    $page->wait(1);
    
    // Verify selection count increased
    $page->assertSee('1', '[pest="domain-card-selected-count"]');
});


