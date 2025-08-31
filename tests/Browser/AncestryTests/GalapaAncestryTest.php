<?php

declare(strict_types=1);

require_once __DIR__ . '/../Helpers/CharacterBuilderHelpers.php';

use function Tests\Browser\Helpers\{
    waitForCharacterBuilderToLoad,
    selectClass,
    selectAncestry
};

test('galapa ancestry provides shell protection', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    // Select a class first to establish baseline stats
    selectClass($page, 'warrior');
    
    // Navigate to ancestry selection and select Galapa
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
        
    selectAncestry($page, 'galapa');
    
    // Should see Galapa-specific features
    $page->assertSee('Galapa')
        ->assertSee('Shell')
        ->assertSee('Retract');
    
    // Check that damage threshold bonus is mentioned
    $page->assertSee('damage thresholds');
});

test('galapa ancestry retract feature is displayed', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
        
    selectAncestry($page, 'galapa');
    
    // Should see the Retract feature description
    $page->assertSee('Retract')
        ->assertSee('Mark a Stress');
});

test('galapa ancestry shows correct description', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
        
    selectAncestry($page, 'galapa');
    
    // Should see Galapa description
    $page->assertSee('Galapa resemble anthropomorphic turtles');
});
