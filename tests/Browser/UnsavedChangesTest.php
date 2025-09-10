<?php

declare(strict_types=1);

it('tracks unsaved changes and shows floating save button', function (): void {
    $page = visit('/character-builder');
    $page->wait(3);

    // Select a class - this should trigger unsaved changes
    $page->assertSee('Choose a Class');
    $page->click('[pest="class-card-bard"]');
    $page->wait(3);

    // Now floating save button should appear
    $page->assertPresent('[pest="floating-save-button"]');

    // Verify the button text
    $page->assertSee('Save Character');
});

// TODO: Add completion section test when navigation issues are resolved
// The banner and View Character button states work correctly, but need
// to reach the completion section reliably in browser tests

it('save button disappears after successful save', function (): void {
    $page = visit('/character-builder');
    $page->wait(2);

    // Make a simple change
    $page->click('[pest="class-card-warrior"]');
    $page->wait(1);

    // Floating save button should appear
    $page->assertPresent('[pest="floating-save-button"]');

    // Click the floating save button
    $page->click('[pest="floating-save-button"]');
    $page->wait(3);

    // After save, floating save button should disappear
    $page->assertDontSee('[pest="floating-save-button"]');
});
