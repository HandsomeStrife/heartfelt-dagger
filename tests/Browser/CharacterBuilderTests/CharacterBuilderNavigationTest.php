<?php

declare(strict_types=1);

/**
 * Basic Character Builder Navigation Test
 * 
 * This test validates that we can navigate through the character builder
 * and see what content is actually available at each step.
 */

test('character builder step navigation', function () {
    // Start at character builder main page
    $page = visit('/character-builder');
    
    // Verify initial page load
    $page->assertSee('Character Builder')
         ->assertSee('Choose a Class')
         ->wait(3);
    
    // Step 1: Select Warrior class
    $page->click('[dusk="class-card-warrior"]')
         ->wait(5) // Give more time for data to load
         ->assertSee('Warrior');
    
    // Navigate to subclass selection  
    $page->click('[dusk="next-step-button"]')
         ->wait(5); // Give more time for subclass data to load
    
    // Check what's actually on the page
    // Just verify we've successfully navigated to the subclass step
    $page->assertSee('Choose Your Subclass');
});
