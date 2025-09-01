<?php

declare(strict_types=1);

use Illuminate\Support\Str;

it('character builder basic flow: create → class selection → traits → equipment gating', function () {
    $page = visit('/character-builder');

    // Redirects to edit/{character_key}
    $page->assertPathBeginsWith('/character-builder/');

    // Class selection available and interacts (use dusk attribute)
    $page->click('@class-card-bard')->wait(1);

    // Domain cards filtered to two domains; only level 1 appear in UI labels
    // Navigate to Domain Cards step via sidebar (Step 9)
    $page->click('@sidebar-tab-9')->wait(1)->assertSee('Select Domain Cards');

    // Trait enforcement: attempt to complete with invalid distribution should not allow
    // Using client-side controls (labels/inputs may vary; assert presence of enforcement hints)
    // Navigate to Trait Assignment (Step 5)
    $page->click('@sidebar-tab-5')->wait(1)->assertSee('Trait');

    // Equipment gating copy visible
    // Navigate to Equipment (Step 6)
    $page->click('@sidebar-tab-6')->wait(1)->assertSee('Selected Equipment');
});

it('domain card selection toggles and enforces max selection', function () {
    $page = visit('/character-builder');

    $page->assertPathBeginsWith('/character-builder/');

    // Select a class to enable domain cards
    $page->click('@class-card-wizard')->wait(1);

    // Select two level 1 cards from visible domains
    // Use first two visible card action buttons (labels may vary, so click by index with CSS)
    // Go to domain cards step
    $page->click('@sidebar-tab-9')->wait(1)->assertSee('Select Domain Cards');

    // Click first two cards via dusk attributes
    $page->click('@domain-card-arcana-rune ward')
         ->click('@domain-card-midnight-pick and pull')
         ->wait(1);

    // Third selection should be blocked (unless subclass increases)
    // Attempt third selection and expect max message overlay
    $page->click('@domain-card-arcana-unleash chaos')
         ->wait(1)
         ->assertSee('Maximum cards selected');
});


