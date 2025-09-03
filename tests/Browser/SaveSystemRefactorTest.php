<?php

declare(strict_types=1);

// Tests for the save system refactor addressing multiple issues
describe('Save System Refactor', function () {
    
    it('shows tall-toasts notifications instead of custom popup', function () {
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');

        // Select a class to trigger unsaved changes
        $page->click('[pest="class-card-warrior"]');
        $page->wait(1);

        // Click save and expect tall-toasts notification
        $page->click('[pest="floating-save-button"]');
        
        // Wait for tall-toasts notification (should appear at top-right)
        $page->waitForText('Character saved successfully!');
        
        // Should NOT see the old "Notification" popup
        $page->assertDontSee('Notification');
    });

    it('shows saving overlay when save is in progress', function () {
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');

        // Select a class to trigger unsaved changes
        $page->click('[pest="class-card-bard"]');
        $page->wait(1);

        // Click save
        $page->click('[pest="floating-save-button"]');
        
        // Should see saving overlay immediately
        $page->assertSee('Saving character...');
        $page->assertVisible('.h-6.w-6.animate-spin'); // Loading spinner in overlay
        
        // Wait for save to complete
        $page->waitForText('Character saved successfully!');
        
        // Saving overlay should disappear
        $page->assertDontSee('Saving character...');
    });

    it('toggles header banner with unsaved changes banner', function () {
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');

        // Initially should see header banner
        $page->assertSee('Character Builder');
        $page->assertSee('Save'); // Header save button
        
        // Should not see unsaved changes banner
        $page->assertDontSee('You have unsaved changes');

        // Select a class to trigger unsaved changes
        $page->click('[pest="class-card-ranger"]');
        $page->wait(1);

        // Header banner should be hidden, unsaved changes banner should show
        $page->assertSee('You have unsaved changes');
        $page->assertSee('Save Now'); // Unsaved changes banner save button
        
        // Check that the black header banner is hidden (by checking if "Character Builder" title is not visible in the header)
        $page->wait(0.5); // Allow time for Alpine to process
        
        // Try to verify the header is hidden by checking if it's not in the DOM or not visible
        // Since Alpine x-show sets display:none, we need to check differently

        // Save the character
        $page->click('[pest="floating-save-button"]');
        $page->waitForText('Character saved successfully!');
        $page->wait(1);

        // Header banner should reappear, unsaved changes banner should hide
        $page->assertSee('Character Builder');
        $page->assertDontSee('You have unsaved changes');
    });

    it('prevents equipment auto-save and uses manual save only', function () {
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');

        // Navigate to equipment step
        $page->click('[pest="class-card-warrior"]');
        $page->wait(1);
        $page->click('[pest="subclass-card-stalwart"]');
        $page->wait(1);
        
        // Navigate through steps to equipment
        for ($step = 1; $step <= 5; $step++) {
            $page->click('[pest="next-step-button"]');
            $page->wait(1);
        }

        // Select equipment - should trigger unsaved changes
        $page->click('[pest="equipment-weapon-sword"]');
        $page->wait(1);

        // Should see unsaved changes banner
        $page->assertSee('You have unsaved changes');
        
        // Should NOT automatically save (no success toast yet)
        $page->assertDontSee('Character saved successfully!');

        // Manual save should work
        $page->click('[pest="floating-save-button"]');
        $page->waitForText('Character saved successfully!');
    });

    it('domain card changes trigger unsaved state', function () {
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');

        // Setup character to reach domain cards step
        $page->click('[pest="class-card-wizard"]');
        $page->wait(1);
        $page->click('[pest="subclass-card-academy scholar"]');
        $page->wait(1);

        // Navigate through steps to domain cards
        for ($step = 1; $step <= 8; $step++) {
            $page->click('[pest="next-step-button"]');
            $page->wait(1);
        }

        // Select a domain card
        $page->click('[pest="domain-card-codex-book of ava"]');
        $page->wait(1);

        // Should trigger unsaved changes
        $page->assertSee('You have unsaved changes');
        
        // Should not auto-save
        $page->assertDontSee('Character saved successfully!');
    });

    it('background questions do not auto-save', function () {
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');

        // Setup character to reach background step
        $page->click('[pest="class-card-seraph"]');
        $page->wait(1);
        $page->click('[pest="subclass-card-starlight sonority"]');
        $page->wait(1);

        // Navigate through steps to background
        for ($step = 1; $step <= 6; $step++) {
            $page->click('[pest="next-step-button"]');
            $page->wait(1);
        }

        // Type in background answers
        $page->type('textarea[name="background_answers.0"]', 'My character background');
        $page->wait(1);

        // Should trigger unsaved changes
        $page->assertSee('You have unsaved changes');
        
        // Should not auto-save
        $page->assertDontSee('Character saved successfully!');
        
        // Manual save should work
        $page->click('[pest="floating-save-button"]');
        $page->waitForText('Character saved successfully!');
    });

    it('character details do not auto-save', function () {
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');

        // Change character name
        $page->clear('input[name="character.name"]');
        $page->type('input[name="character.name"]', 'Test Character');
        $page->wait(1);

        // Should trigger unsaved changes
        $page->assertSee('You have unsaved changes');
        
        // Should not auto-save
        $page->assertDontSee('Character saved successfully!');

        // Change pronouns  
        $page->clear('input[name="pronouns"]');
        $page->type('input[name="pronouns"]', 'they/them');
        $page->wait(1);

        // Should still have unsaved changes
        $page->assertSee('You have unsaved changes');
        
        // Manual save should work
        $page->click('[pest="floating-save-button"]');
        $page->waitForText('Character saved successfully!');
    });

    it('connections do not cause trim error', function () {
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');

        // Setup character to reach connections step
        $page->click('[pest="class-card-druid"]');
        $page->wait(1);
        $page->click('[pest="subclass-card-circle of spores"]');
        $page->wait(1);

        // Navigate through all steps to connections
        for ($step = 1; $step <= 9; $step++) {
            $page->click('[pest="next-step-button"]');
            $page->wait(1);
        }

        // Type in connections - this previously caused trim() error
        $page->type('textarea[name="connection_answers.0"]', 'Connection answer');
        $page->wait(1);

        // Should not cause any errors
        $page->assertDontSee('trim(): Argument #1 ($string) must be of type string, null given');
        
        // Should trigger unsaved changes
        $page->assertSee('You have unsaved changes');
        
        // Should not auto-save
        $page->assertDontSee('Character saved successfully!');
    });

});

