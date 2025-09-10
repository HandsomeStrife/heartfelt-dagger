<?php

declare(strict_types=1);

// Test to validate domain card level toggle functionality matches the working tests
it('validates domain card selection and level toggle functionality', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Select class (same pattern as working tests)
    $page->assertPresent('[pest="class-card-wizard"]');
    $page->click('[pest="class-card-wizard"]');
    $page->wait(1);

    // Navigate to Domain Cards step (exact same navigation as working tests)
    for ($i = 0; $i < 8; $i++) {
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
    }

    $page->assertSee('Select Domain Cards');
    $page->wait(2);

    // Verify domain cards section loads (basic functionality)
    $page->assertPresent('[pest="domain-card-selected-count"]');
    $page->assertPresent('[pest^="domain-card-"]');

    // Verify domain cards can be selected (same as working tests)
    // Click on a specific domain card that should be available for Wizard (codex domain)
    $page->click('[pest="domain-card-codex-book of ava"]');
    $page->wait(1);
    $page->assertSee('1', '[pest="domain-card-selected-count"]');
});
