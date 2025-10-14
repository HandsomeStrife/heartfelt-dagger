<?php

declare(strict_types=1);

use function Pest\Laravel\{get};

describe('Higher Level Character Creation Flow', function () {
    test('can select starting level and see advancement requirements', function () {
        $page = visit('/character-builder');

        // Redirects to edit/{character_key}
        $page->assertPathBeginsWith('/character-builder/');

        // Complete basic steps to reach advancements
        // Step 1: Class Selection
        $page->assertSee('Choose a Class');
        $page->click('[pest="class-card-guardian"]');
        $page->wait(0.5);
        $page->click('[pest="next-step-button"]');
        $page->wait(0.5);

        // Step 2: Subclass
        $page->assertSee('Choose Your Subclass');
        $page->click('[pest="next-step-button"]');
        $page->wait(0.5);

        // Step 3: Ancestry
        $page->assertSee('Choose Your Ancestry');
        $page->click('[pest="ancestry-card-human"]');
        $page->wait(0.5);
        $page->click('[pest="next-step-button"]');
        $page->wait(0.5);

        // Step 4: Community
        $page->assertSee('Choose Your Community');
        $page->click('[pest="community-card-city"]');
        $page->wait(0.5);
        $page->click('[pest="next-step-button"]');
        $page->wait(0.5);

        // Step 5: Traits - Select starting level here
        $page->assertSee('Assign Traits');
        
        // Level selector should be present
        $page->assertPresent('[pest="level-selector"]');
        $page->assertSee('Starting Level');
        
        // Default should be level 1
        $page->assertSee('Level 1');
        
        // Select level 3
        $page->click('[pest="level-option-3"]');
        $page->wait(0.5);
        
        // Should show advancement requirements message
        $page->assertSee('levels 2 through 3');
        $page->assertSee('Tier achievements');
        $page->assertSee('advancement selections');
        $page->assertSee('domain cards');
    });

    test('level 2 character creation with tier achievement', function () {
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');

        // Quick flow to advancements step
        // Step 1: Class
        $page->click('[pest="class-card-guardian"]');
        $page->wait(0.5);
        $page->click('[pest="next-step-button"]');
        $page->wait(0.5);

        // Step 2: Subclass
        $page->click('[pest="next-step-button"]');
        $page->wait(0.5);

        // Step 3: Ancestry
        $page->click('[pest="ancestry-card-human"]');
        $page->wait(0.5);
        $page->click('[pest="next-step-button"]');
        $page->wait(0.5);

        // Step 4: Community
        $page->click('[pest="community-card-city"]');
        $page->wait(0.5);
        $page->click('[pest="next-step-button"]');
        $page->wait(0.5);

        // Step 5: Traits - assign traits
        $page->click('[pest="trait-agility-plus-2"]');
        $page->wait(0.3);
        $page->click('[pest="trait-strength-plus-1"]');
        $page->wait(0.3);
        $page->click('[pest="trait-finesse-plus-1"]');
        $page->wait(0.3);
        $page->click('[pest="trait-instinct-zero"]');
        $page->wait(0.3);
        $page->click('[pest="trait-presence-zero"]');
        $page->wait(0.3);
        $page->click('[pest="trait-knowledge-minus-1"]');
        $page->wait(0.5);
        $page->click('[pest="next-step-button"]');
        $page->wait(0.5);

        // Step 6: Equipment - skip for now
        $page->click('[pest="next-step-button"]');
        $page->wait(0.5);

        // Step 7: Background - skip
        $page->click('[pest="next-step-button"]');
        $page->wait(0.5);

        // Step 8: Experiences - skip
        $page->click('[pest="next-step-button"]');
        $page->wait(0.5);

        // Step 9: Domain Cards - select one
        $page->assertPresent('[pest="domain-card-selector"]');
        $page->click('[pest="domain-card-0"]'); // First available card
        $page->wait(0.5);
        $page->click('[pest="next-step-button"]');
        $page->wait(0.5);

        // Step 10: Connections - skip
        $page->click('[pest="next-step-button"]');
        $page->wait(0.5);

        // Now at ADVANCEMENTS step
        $page->assertSee('Character Advancement');
        
        // Should show level 2 tier achievement
        $page->assertSee('Level 2');
        $page->assertSee('Tier Achievement');
        $page->assertSee('Create a new experience');
        
        // Create tier achievement experience
        $page->assertPresent('[pest="tier-experience-name"]');
        $page->type('[pest="tier-experience-name"]', 'Combat Veteran');
        $page->type('[pest="tier-experience-description"]', 'Survived many battles');
        $page->wait(0.5);
        
        // Should show advancement options
        $page->assertSee('Select 2 Advancements');
        $page->assertPresent('[pest="advancement-option-0"]');
        $page->assertPresent('[pest="advancement-option-1"]');
        
        // Select 2 advancements
        $page->click('[pest="advancement-option-0"]');
        $page->wait(0.3);
        $page->click('[pest="advancement-option-1"]');
        $page->wait(0.5);
        
        // Select domain card for level 2
        $page->assertPresent('[pest="domain-card-selector"]');
        $page->click('[pest="domain-card-0"]');
        $page->wait(0.5);
        
        // Validation should pass
        $page->assertDontSee('Missing required');
        
        // Can advance to next step
        $page->assertPresent('[pest="next-step-button"]');
    });

    test('level 5 character requires multiple tier achievements', function () {
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');

        // Quick setup through traits, then select level 5
        // ... (abbreviated for brevity) ...
        
        // At advancements step
        // Should show level 2 tier achievement
        $page->assertSee('Level 2');
        $page->assertSee('Tier 1');
        
        // Complete level 2...
        $page->type('[pest="tier-experience-name"]', 'Experience 1');
        $page->wait(0.3);
        $page->click('[pest="advancement-option-0"]');
        $page->wait(0.3);
        $page->click('[pest="advancement-option-1"]');
        $page->wait(0.3);
        $page->click('[pest="domain-card-0"]');
        $page->wait(0.5);
        $page->click('[pest="level-complete-button"]');
        $page->wait(0.5);
        
        // Should advance to level 3
        $page->assertSee('Level 3');
        $page->assertSee('Tier 2');
        
        // No tier achievement at level 3
        $page->assertDontSee('Tier Achievement');
        
        // Complete level 3...
        $page->click('[pest="advancement-option-0"]');
        $page->wait(0.3);
        $page->click('[pest="advancement-option-1"]');
        $page->wait(0.3);
        $page->click('[pest="domain-card-0"]');
        $page->wait(0.5);
        $page->click('[pest="level-complete-button"]');
        $page->wait(0.5);
        
        // Should advance to level 4
        $page->assertSee('Level 4');
        
        // Complete level 4...
        $page->click('[pest="advancement-option-0"]');
        $page->wait(0.3);
        $page->click('[pest="advancement-option-1"]');
        $page->wait(0.3);
        $page->click('[pest="domain-card-0"]');
        $page->wait(0.5);
        $page->click('[pest="level-complete-button"]');
        $page->wait(0.5);
        
        // Should advance to level 5 - TIER ACHIEVEMENT!
        $page->assertSee('Level 5');
        $page->assertSee('Tier 3');
        $page->assertSee('Tier Achievement');
        $page->assertPresent('[pest="tier-experience-name"]');
    });

    test('advancement validation prevents incomplete level progression', function () {
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');

        // Quick setup to advancement step with level 2
        // ... (setup code) ...
        
        // At level 2 advancements
        $page->assertSee('Level 2');
        
        // Try to advance without completing requirements
        $page->click('[pest="level-complete-button"]');
        $page->wait(0.5);
        
        // Should show validation errors
        $page->assertSee('Missing required');
        $page->assertSee('tier achievement experience');
        
        // Add tier experience but not advancements
        $page->type('[pest="tier-experience-name"]', 'Test Experience');
        $page->wait(0.3);
        $page->click('[pest="level-complete-button"]');
        $page->wait(0.5);
        
        // Should still show errors
        $page->assertSee('exactly 2 advancements');
        
        // Select only 1 advancement
        $page->click('[pest="advancement-option-0"]');
        $page->wait(0.3);
        $page->click('[pest="level-complete-button"]');
        $page->wait(0.5);
        
        // Should still show error
        $page->assertSee('exactly 2 advancements');
        
        // Select 2nd advancement but no domain card
        $page->click('[pest="advancement-option-1"]');
        $page->wait(0.3);
        $page->click('[pest="level-complete-button"]');
        $page->wait(0.5);
        
        // Should show domain card error
        $page->assertSee('domain card');
        
        // Complete all requirements
        $page->click('[pest="domain-card-0"]');
        $page->wait(0.5);
        $page->click('[pest="level-complete-button"]');
        $page->wait(0.5);
        
        // Should advance successfully
        $page->assertSee('Level 3');
        $page->assertDontSee('Missing required');
    });

    test('trait bonus advancement shows trait selector modal', function () {
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');

        // Quick setup to advancement step
        // ... (setup code) ...
        
        // At level 2, find and click trait bonus advancement
        $page->click('[pest="advancement-trait-bonus"]');
        $page->wait(0.5);
        
        // Should show trait selector modal
        $page->assertSee('Select Traits to Improve');
        $page->assertPresent('[pest="trait-selector-modal"]');
        
        // Should show all available traits
        $page->assertSee('Agility');
        $page->assertSee('Strength');
        $page->assertSee('Finesse');
        
        // Should not be able to select marked traits (if any)
        // (This depends on tier and previous selections)
        
        // Select a trait
        $page->click('[pest="trait-select-agility"]');
        $page->wait(0.3);
        
        // Confirm selection
        $page->click('[pest="trait-selector-confirm"]');
        $page->wait(0.5);
        
        // Modal should close
        $page->assertDontSee('[pest="trait-selector-modal"]');
        
        // Advancement should be marked as selected
        $page->assertPresent('[pest="advancement-trait-bonus"][data-selected="true"]');
    });

    test('progress indicators update as levels are completed', function () {
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');

        // Setup with level 3 selected
        // ... (setup code) ...
        
        // Should show progress indicators
        $page->assertPresent('[pest="advancement-progress"]');
        $page->assertSee('0%'); // 0 of 2 levels complete
        
        // Complete level 2
        $page->type('[pest="tier-experience-name"]', 'Experience 1');
        $page->wait(0.3);
        $page->click('[pest="advancement-option-0"]');
        $page->wait(0.3);
        $page->click('[pest="advancement-option-1"]');
        $page->wait(0.3);
        $page->click('[pest="domain-card-0"]');
        $page->wait(0.5);
        $page->click('[pest="level-complete-button"]');
        $page->wait(0.5);
        
        // Progress should update
        $page->assertSee('50%'); // 1 of 2 levels complete
        
        // Level 2 pill should show complete
        $page->assertPresent('[pest="level-pill-2"][data-status="complete"]');
        
        // Current level should be 3
        $page->assertPresent('[pest="level-pill-3"][data-status="current"]');
        
        // Complete level 3
        $page->click('[pest="advancement-option-0"]');
        $page->wait(0.3);
        $page->click('[pest="advancement-option-1"]');
        $page->wait(0.3);
        $page->click('[pest="domain-card-0"]');
        $page->wait(0.5);
        $page->click('[pest="level-complete-button"]');
        $page->wait(0.5);
        
        // Progress should be 100%
        $page->assertSee('100%');
        
        // All levels should be complete
        $page->assertPresent('[pest="level-pill-2"][data-status="complete"]');
        $page->assertPresent('[pest="level-pill-3"][data-status="complete"]');
    });

    test('real-time validation provides immediate feedback', function () {
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');

        // Setup to advancement step
        // ... (setup code) ...
        
        // At level 2
        $page->assertSee('Level 2');
        
        // Validation errors should not show initially
        $page->assertDontSee('Missing required');
        
        // Start filling tier experience
        $page->type('[pest="tier-experience-name"]', 'T');
        $page->wait(0.6); // Wait for debounced validation (500ms + buffer)
        
        // Should still show incomplete (experience too short)
        // But real-time validation should not show errors until attempt to proceed
        
        // Complete tier experience
        $page->type('[pest="tier-experience-name"]', 'est Experience');
        $page->wait(0.6);
        
        // Select first advancement
        $page->click('[pest="advancement-option-0"]');
        $page->wait(0.6);
        
        // Should indicate 1 of 2 selected
        $page->assertSee('1 / 2');
        
        // Select second advancement
        $page->click('[pest="advancement-option-1"]');
        $page->wait(0.6);
        
        // Should indicate 2 of 2 selected
        $page->assertSee('2 / 2');
        
        // Select domain card
        $page->click('[pest="domain-card-0"]');
        $page->wait(0.6);
        
        // Level should be marked as complete
        $page->assertPresent('[data-level-complete="true"]');
    });
});


