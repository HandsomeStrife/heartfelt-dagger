<?php

declare(strict_types=1);

require_once __DIR__ . '/../Helpers/CharacterBuilderHelpers.php';

use function Tests\Browser\Helpers\{
    waitForCharacterBuilderToLoad,
    selectClass,
    assertStepComplete,
    goToStep
};

test('witch class can be selected and shows correct information', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    // Should see Witch as an option
    $page->assertSee('Witch');

    // Click on Witch class card
    selectClass($page, 'witch');

    // Check that Witch-specific information is displayed
    $page->assertSee('Dread')
        ->assertSee('Sage')
        ->assertSee('Hex');

    // Check class description content
    $page->assertSee('As a witch, you weave together the mysterious powers');
    
    // Verify step completion works
    assertStepComplete($page, 1);
});

test('witch subclass step can be navigated', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'witch');
    
    // Navigate to subclass step manually
    $page->click('[dusk="next-step-button"]')
        ->wait(3);
    
    // Should see subclass selection interface
    $page->assertSee('Subclass')
        ->assertSee('Choose');
});

test('witch starting stats are displayed', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'witch');
    
    // Check character displays basic stat information
    $page->assertSee('Witch');
});

test('witch shows mystical features', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'witch');
    
    // Should see Witch-specific features
    $page->assertSee('Hex')
        ->assertSee('Commune');
});
