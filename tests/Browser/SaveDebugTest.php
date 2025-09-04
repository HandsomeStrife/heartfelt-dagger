<?php

declare(strict_types=1);

// Debug test to understand save button behavior
it('debugs save button behavior step by step', function () {
    $page = visit('/character-builder');
    $page->assertPathBeginsWith('/character-builder/');

    // Step 1: Verify initial state
    $page->assertDontSee('You have unsaved changes');
    $page->wait(2)->assertSee('Choose a Class'); // Ensure page is loaded
    
    // Step 2: Select a class
    $page->click('[pest="class-card-warrior"]');
    $page->wait(1);
    
    // Step 3: Verify unsaved changes state
    $page->assertSee('You have unsaved changes');
    $page->assertVisible('[pest="floating-save-button"]');
    
    // Step 4: Try to click save and see what happens
    $page->click('[pest="floating-save-button"]');
    $page->wait(3); // Give more time for any response
    
    // Check if any notification appears at all
    $page->assertSee('Character saved successfully!');
    
    // OR check for any error notification
    // $page->assertDontSee('Failed to save');
});

