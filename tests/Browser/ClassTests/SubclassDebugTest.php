<?php

declare(strict_types=1);

require_once __DIR__ . '/../Helpers/CharacterBuilderHelpers.php';

use function Tests\Browser\Helpers\{
    waitForCharacterBuilderToLoad,
    selectClass
};

test('debug subclass step navigation', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'druid');
    
    // Try to go to subclass step manually with extra debugging
    $page->click('[dusk="next-step-button"]')
        ->wait(5);  // Longer wait
    
    // Take a screenshot to see what's on the page
    $page->screenshot(filename: 'subclass-debug');
    
    // Check if we can see ANY text suggesting we're in subclass selection
    $page->assertSee('Subclass')
        ->assertSee('Choose');
});

test('debug what druid subclasses are shown', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'druid');
    
    // Try to go to subclass step manually
    $page->click('[dusk="next-step-button"]')
        ->wait(5);
    
    // Check for various possible subclass names
    $foundSubclass = false;
    
    $possibleNames = ['Hedge', 'hedge', 'Hedge Witch', 'hedge witch', 'Moon', 'moon', 'Moon Druid', 'moon druid'];
    
    foreach ($possibleNames as $name) {
        if ($page->visit('/character-builder') && waitForCharacterBuilderToLoad($page) && selectClass($page, 'druid')) {
            $page->click('[dusk="next-step-button"]')->wait(3);
            try {
                $page->assertSee($name);
                $foundSubclass = true;
                break;
            } catch (Exception $e) {
                // Continue to next name
            }
        }
    }
    
    expect($foundSubclass)->toBeTrue('Should find at least one druid subclass name');
});
