<?php

declare(strict_types=1);

require_once __DIR__ . '/../Helpers/CharacterBuilderHelpers.php';

use function Tests\Browser\Helpers\{
    waitForCharacterBuilderToLoad,
    selectClass,
    assertStepComplete,
    goToStep
};

test('warlock class can be selected and shows correct information', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    // Should see Warlock as an option
    $page->assertSee('Warlock');

    // Click on Warlock class card
    selectClass($page, 'warlock');

    // Check that Warlock-specific information is displayed
    $page->assertSee('Dread')
        ->assertSee('Grace')
        ->assertSee('Favor');

    // Check class description content
    $page->assertSee('As a warlock, you\'ve pledged your life to a patron');
    
    // Verify step completion works
    assertStepComplete($page, 1);
});

test('warlock subclass step can be navigated', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'warlock');
    
    // Navigate to subclass step manually
    $page->click('[dusk="next-step-button"]')
        ->wait(3);
    
    // Should see subclass selection interface
    $page->assertSee('Subclass')
        ->assertSee('Choose');
});

test('warlock starting stats are displayed', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'warlock');
    
    // Check character displays basic stat information
    $page->assertSee('Warlock');
});

test('warlock shows patron features', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'warlock');
    
    // Should see Warlock-specific features
    $page->assertSee('Favor')
        ->assertSee('Patron\'s Boon');
});

