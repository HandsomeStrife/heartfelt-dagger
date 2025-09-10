<?php

declare(strict_types=1);

// Test that save button and warning banner behavior works correctly
it('hides save button and warning banner after successful save', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Initially no unsaved changes - save button should not be visible
    $page->assertDontSee('You have unsaved changes');

    // Select a class to trigger unsaved changes
    $page->click('[pest="class-card-warrior"]');
    $page->wait(1);

    // Warning banner should appear
    $page->assertSee('You have unsaved changes');

    // Floating save button should appear
    $page->assertVisible('[pest="floating-save-button"]');

    // Click save
    $page->click('[pest="floating-save-button"]');

    // Wait for potential save (Livewire calls don't work in browser tests)
    $page->wait(2);
    $page->wait(2); // Give time for JavaScript state to update

    // Warning banner should disappear
    $page->assertDontSee('You have unsaved changes');

    // Floating save button should disappear (hidden by x-show="hasUnsavedChanges")
    $page->assertNotVisible('[pest="floating-save-button"]');
});

it('shows and hides main save button based on unsaved changes', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Select a class to trigger unsaved changes
    $page->click('[pest="class-card-bard"]');
    $page->wait(1);

    // Main save button should be enabled
    $page->assertVisible('[pest="save-character-button"]');
    $page->assertAttributeMissing('[pest="save-character-button"]', 'disabled');

    // Click the main save button
    $page->click('[pest="save-character-button"]');

    // Wait for save to complete
    $page->waitForText('Character saved successfully!');
    $page->wait(2);

    // Main save button should be disabled (no unsaved changes)
    $page->assertAttribute('[pest="save-character-button"]', 'disabled', 'disabled');
});
