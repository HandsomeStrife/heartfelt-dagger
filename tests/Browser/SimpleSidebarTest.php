<?php

declare(strict_types=1);

// Simple test to verify step completion behavior
it('shows step completion after selecting class and saving', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Select a class
    $page->click('[pest="class-card-warrior"]');
    $page->wait(1);

    // Force save to trigger completion update
    $page->click('[pest="save-character-button"]');
    $page->wait(2); // Wait for potential save (Livewire calls don't work in browser tests)
    $page->wait(2);

    // Now step 1 should be complete - look for any completion indicators
    $page->assertSeeInElement('[pest="sidebar-tab-1"]', 'Completed');
});

it('verifies class selection works without errors', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Select a class and verify no errors
    $page->click('[pest="class-card-bard"]');
    $page->wait(1);

    // Verify the class was selected by checking for subclass options
    $page->assertSee('Choose a Subclass');
    $page->assertSee('College of Lore');
    $page->assertSee('Starlight Sonority');
});
