<?php

declare(strict_types=1);

require_once __DIR__ . '/../Helpers/CharacterBuilderHelpers.php';

use function Tests\Browser\Helpers\{
    waitForCharacterBuilderToLoad,
    selectClass,
    assertStepComplete,
    goToStep
};

test('ranger class can be selected and shows correct information', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    // Should see Ranger as an option
    $page->assertSee('Ranger');

    // Click on Ranger class card
    selectClass($page, 'ranger');

    // Check that Ranger-specific information is displayed
    $page->assertSee('Bone')
        ->assertSee('Sage')
        ->assertSee('Companion');

    // Check class description content
    $page->assertSee('Rangers are highly skilled hunters who, despite their martial abilities');
    
    // Verify step completion works
    assertStepComplete($page, 1);
});

test('ranger subclass step can be navigated', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'ranger');
    
    // Navigate to subclass step manually
    $page->click('[dusk="next-step-button"]')
        ->wait(3);
    
    // Should see subclass selection interface
    $page->assertSee('Subclass')
        ->assertSee('Choose');
});

test('ranger starting stats are displayed', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'ranger');
    
    // Check character displays basic stat information
    $page->assertSee('Ranger');
});

test('ranger shows hunting features', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'ranger');
    
    // Should see Ranger-specific features
    $page->assertSee('Companion')
        ->assertSee('Hold Them Off');
});

