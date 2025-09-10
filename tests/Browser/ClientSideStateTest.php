<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

// Test client-side state management and computed properties
it('computed properties work correctly in JavaScript', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Select class and verify computed properties update
    $page->click('[pest="class-card-wizard"]');
    $page->wait(1);

    // Should show available subclasses immediately (computed from selectedClassData)
    $page->assertSee('Hedge Magic');
    $page->assertSee('School of the Arcane');

    // Navigate to ancestry and verify heritage computed properties
    $page->click('[pest="next-step-button"]'); // Subclass
    $page->wait(1);
    $page->click('[pest="next-step-button"]'); // Ancestry
    $page->wait(1);

    $page->click('[pest="ancestry-card-clank"]');
    $page->wait(1);

    // Navigate to experience step to test Clank bonus computation
    for ($i = 0; $i < 5; $i++) {
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
    }

    // Verify Clank bonus experience functionality (computed property)
    $page->type('[dusk="new-experience-name"]', 'Engineering');
    $page->click('[dusk="add-experience-button"]');
    $page->wait(1);

    // Should show bonus selection option for Clank ancestry
    $page->assertSee('Experience Bonus Selection');

    // Click on experience to select as bonus
    $page->click('[dusk="experience-card-0"]');
    $page->wait(1);

    // Should show +3 modifier instead of +2
    $page->assertSee('+3');
});

it('form validation works client-side', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Navigate to experience step
    $page->click('[pest="class-card-warrior"]');
    $page->wait(1);

    for ($i = 0; $i < 7; $i++) {
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
    }

    // Verify add button is disabled when name is empty (client-side validation)
    $page->assertDisabled('[dusk="add-experience-button"]');

    // Add text and verify button becomes enabled
    $page->type('[dusk="new-experience-name"]', 'Test');
    $page->wait(0.5);
    $page->assertEnabled('[dusk="add-experience-button"]');

    // Clear text and verify button becomes disabled again
    $page->clear('[dusk="new-experience-name"]');
    $page->wait(0.5);
    $page->assertDisabled('[dusk="add-experience-button"]');
});

it('progress indicators update automatically', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Select class and navigate to background
    $page->click('[pest="class-card-bard"]');
    $page->wait(1);

    for ($i = 0; $i < 6; $i++) {
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
    }

    // Initially no progress
    $page->assertDontSee('Background Creation In Progress');

    // Fill in one answer
    $page->type('textarea[dusk="background-question-0"]', 'Test answer');
    $page->wait(1);

    // Should show progress automatically
    $page->assertSee('answered out of');

    // Fill in more answers to test progress updates
    $page->type('textarea[dusk="background-question-1"]', 'Another answer');
    $page->wait(1);

    // Progress should update automatically
    $page->assertSee('2 answered');
});

it('equipment selections update inventory state', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Select class and navigate to equipment
    $page->click('[pest="class-card-ranger"]');
    $page->wait(1);

    for ($i = 0; $i < 4; $i++) {
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
    }

    // Verify we're on equipment step
    $page->assertSee('Select Equipment');

    // Select a primary weapon
    $page->click('[pest^="weapon-"] >> nth=0');
    $page->wait(1);

    // Should see it in selected equipment area
    $page->assertSee('Selected Equipment');

    // Select armor
    $page->click('[pest^="armor-"] >> nth=0');
    $page->wait(1);

    // Both should be visible in selected area
    $page->assertVisible('.selected-equipment');

    // Verify unsaved changes tracking
    $page->assertSee('Unsaved Changes');
});

it('trait assignment validation works client-side', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Navigate to trait assignment
    $page->click('[pest="class-card-sorcerer"]');
    $page->wait(1);

    for ($i = 0; $i < 3; $i++) {
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
    }

    // Verify we're on trait assignment step
    $page->assertSee('Assign Character Traits');

    // Should show all available values for assignment
    $page->assertSee('-1');
    $page->assertSee('0');
    $page->assertSee('+1');
    $page->assertSee('+2');

    // Apply suggested traits and verify validation passes
    $page->click('[pest="apply-suggested-traits"]');
    $page->wait(1);

    // Should show completion indicator
    $page->assertVisible('.trait-assignment-complete');

    // All values should be assigned (no more available values)
    $page->assertDontSee('Available Values');
});

it('character info updates work client-side', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Navigate to character info step
    for ($i = 0; $i < 9; $i++) {
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
    }

    // Should be on character info step
    $page->assertSee('Character Information');

    // Enter character name and verify immediate update
    $page->type('input[dusk="character-name"]', 'Thorin Ironforge');
    $page->wait(1);

    // Name should appear in character summary immediately
    $page->assertSee('Thorin Ironforge');

    // Verify unsaved changes tracking for name changes
    $page->assertSee('Unsaved Changes');
});

it('domain visibility toggles work instantly', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Select a class
    $page->click('[pest="class-card-warrior"]');
    $page->wait(1);

    // Navigate to domain cards step
    for ($i = 0; $i < 8; $i++) {
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
    }

    // Should only see warrior domains (Blade + Bone)
    $page->assertSee('Blade Domain');
    $page->assertSee('Bone Domain');
    $page->assertDontSee('Grace Domain');
    $page->assertDontSee('Arcana Domain');

    // Go back and change class
    $page->click('[pest="step-tab-1"]'); // Go back to class selection
    $page->wait(1);

    $page->click('[pest="class-card-bard"]');
    $page->wait(1);

    // Navigate back to domain cards
    for ($i = 0; $i < 8; $i++) {
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
    }

    // Should now see bard domains (Grace + Codex)
    $page->assertSee('Grace Domain');
    $page->assertSee('Codex Domain');
    $page->assertDontSee('Blade Domain');
    $page->assertDontSee('Bone Domain');
});

it('step navigation preserves JavaScript state', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Make selections
    $page->click('[pest="class-card-druid"]');
    $page->wait(1);

    $page->click('[pest="subclass-card-nature spirit"]');
    $page->wait(1);

    // Navigate forward to ancestry
    $page->click('[pest="next-step-button"]');
    $page->wait(1);

    $page->click('[pest="ancestry-card-dwarf"]');
    $page->wait(1);

    // Navigate back to class selection
    $page->click('[pest="step-tab-1"]');
    $page->wait(1);

    // Verify selections are preserved
    $page->assertSee('Druid');

    // Navigate to subclass
    $page->click('[pest="step-tab-2"]');
    $page->wait(1);
    $page->assertSee('Nature Spirit');

    // Navigate to ancestry
    $page->click('[pest="step-tab-3"]');
    $page->wait(1);
    $page->assertSee('Dwarf');

    // All state should be preserved across navigation
    $page->assertSee('Unsaved Changes'); // State preserved
});
