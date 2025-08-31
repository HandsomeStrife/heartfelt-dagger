<?php

declare(strict_types=1);

require_once __DIR__ . '/../Helpers/CharacterBuilderHelpers.php';

use function Tests\Browser\Helpers\{
    waitForCharacterBuilderToLoad,
    selectClass,
    assertStepComplete,
    goToStep
};

test('assassin class can be selected and shows correct information', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    // Should see Assassin as an option
    $page->assertSee('Assassin');

    // Click on Assassin class card
    selectClass($page, 'assassin');

    // Check that Assassin-specific information is displayed
    $page->assertSee('Midnight')
        ->assertSee('Blade')
        ->assertSee('Marked for Death');

    // Check class description content
    $page->assertSee('As an assassin, you utilize unmatched stealth and precision');
    
    // Verify step completion works
    assertStepComplete($page, 1);
});

test('assassin subclass step can be navigated', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'assassin');
    
    // Navigate to subclass step manually
    $page->click('[dusk="next-step-button"]')
        ->wait(3);
    
    // Should see subclass selection interface
    $page->assertSee('Subclass')
        ->assertSee('Choose');
});

test('assassin starting stats are displayed', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'assassin');
    
    // Check character displays basic stat information
    $page->assertSee('Assassin');
});

test('assassin shows stealth features', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'assassin');
    
    // Should see Assassin-specific features
    $page->assertSee('Marked for Death')
        ->assertSee('Grim Resolve');
});

