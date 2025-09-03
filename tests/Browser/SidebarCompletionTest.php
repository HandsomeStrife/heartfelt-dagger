<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

// Test that sidebar completion indicators update properly when steps are completed
it('updates sidebar completion indicators in real-time', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Initially no steps should be complete
    $page->assertDontSee('bg-emerald-500'); // No green completion indicators

    // Select a class - Step 1 should become complete
    $page->click('[pest="class-card-warrior"]');
    $page->wait(1); // Wait for the completion state to update

    // Step 1 should now show as complete in sidebar (green indicator)
    $page->assertVisible('[pest="sidebar-tab-1"]');
    // The step 1 button should have completion styling
    $page->assertVisible('[pest="sidebar-tab-1"] .bg-emerald-500'); // Green completion icon

    // Select subclass - Step 2 should become complete  
    $page->click('[pest="subclass-card-stalwart"]');
    $page->wait(1);

    // Step 2 should now show as complete in sidebar
    $page->assertVisible('[pest="sidebar-tab-2"] .bg-emerald-500'); // Green completion icon

    // Navigate to ancestry step
    $page->click('[pest="next-step-button"]');
    $page->wait(1);

    // Select ancestry - Step 3 should become complete
    $page->click('[pest="ancestry-card-human"]');
    $page->wait(1);

    // Step 3 should now show as complete in sidebar
    $page->assertVisible('[pest="sidebar-tab-3"] .bg-emerald-500'); // Green completion icon

    // Navigate to community step  
    $page->click('[pest="next-step-button"]');
    $page->wait(1);

    // Select community - Step 4 should become complete
    $page->click('[pest="community-card-loreborne"]');
    $page->wait(1);

    // Step 4 should now show as complete in sidebar
    $page->assertVisible('[pest="sidebar-tab-4"] .bg-emerald-500'); // Green completion icon

    // Verify multiple steps show as complete
    $page->assertVisible('[pest="sidebar-tab-1"] .bg-emerald-500')
        ->assertVisible('[pest="sidebar-tab-2"] .bg-emerald-500')
        ->assertVisible('[pest="sidebar-tab-3"] .bg-emerald-500')
        ->assertVisible('[pest="sidebar-tab-4"] .bg-emerald-500');
});

it('shows completion status correctly in sidebar text', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Select class and verify completion text
    $page->click('[pest="class-card-bard"]');
    $page->wait(1);

    // Step 1 should show "Completed" text
    $page->click('[pest="sidebar-tab-1"]'); // Click to see the detail
    $page->assertSee('Completed'); // Should show completion status

    // Select subclass  
    $page->click('[pest="subclass-card-college of lore"]');
    $page->wait(1);

    // Step 2 should also show "Completed" text
    $page->click('[pest="sidebar-tab-2"]');
    $page->assertSee('Completed');
});
