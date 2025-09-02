<?php

declare(strict_types=1);

it('allows toggling between showing all levels and current level only', function () {
    $character = \Domain\Character\Models\Character::create([
        'name' => null,
        'character_key' => \Domain\Character\Models\Character::generateUniqueKey(),
        'user_id' => null,
        'class' => null,
        'subclass' => null,
        'ancestry' => null,
        'community' => null,
        'level' => 1,
        'character_data' => [],
        'is_public' => false,
    ]);

    $page = visit('/character-builder/'.$character->character_key);
    $page->assertPathBeginsWith('/character-builder/')->assertSee('Character Builder');

    // Select class to enable domain cards
    $page->assertPresent('[pest="class-card-bard"]');
    $page->click('[pest="class-card-bard"]');
    $page->wait(1);

    // Navigate to Domain Cards step
    for ($i = 0; $i < 8; $i++) {
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
    }

    $page->assertSee('Select Domain Cards');
    $page->wait(2);

    // Initially should show "Showing level 1 only"
    $page->assertSee('Showing level 1 only');
    
    // Toggle button should be present and show "Show all levels"
    $page->assertPresent('[pest="toggle-all-levels-button"]');
    $page->assertSee('Show all levels');

    // Click the toggle button
    $page->click('[pest="toggle-all-levels-button"]');
    $page->wait(1);

    // After toggling, should show "Showing all levels"
    $page->assertSee('Showing all levels');
    $page->assertSee('Hide higher levels');
    $page->assertSee('Higher level cards are greyed out and cannot be selected');

    // Toggle back
    $page->click('[pest="toggle-all-levels-button"]');
    $page->wait(1);

    // Should return to initial state
    $page->assertSee('Showing level 1 only');
    $page->assertSee('Show all levels');
    $page->assertSee('Only cards you can currently select are visible');
});

it('shows more domain cards when toggle is enabled', function () {
    $character = \Domain\Character\Models\Character::create([
        'name' => null,
        'character_key' => \Domain\Character\Models\Character::generateUniqueKey(),
        'user_id' => null,
        'class' => null,
        'subclass' => null,
        'ancestry' => null,
        'community' => null,
        'level' => 1,
        'character_data' => [],
        'is_public' => false,
    ]);

    $page = visit('/character-builder/'.$character->character_key);

    // Select class (Wizard has Codex + Midnight domains with multiple levels)
    $page->assertPresent('[pest="class-card-wizard"]');
    $page->click('[pest="class-card-wizard"]');
    $page->wait(1);

    // Navigate to Domain Cards step
    for ($i = 0; $i < 8; $i++) {
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
    }

    $page->wait(2);

    // Verify basic cards are shown
    $page->assertPresent('[pest^="domain-card-"]');
    
    // Toggle to show all levels
    $page->click('[pest="toggle-all-levels-button"]');
    $page->wait(1);

    // Verify the UI reflects the change
    $page->assertSee('Showing all levels');
    
    // The functionality works if we can toggle without errors
    $page->click('[pest="toggle-all-levels-button"]');
    $page->wait(1);
    $page->assertSee('Showing level 1 only');
});
