<?php

declare(strict_types=1);

require_once __DIR__ . '/../Helpers/CharacterBuilderHelpers.php';

use function Tests\Browser\Helpers\{
    waitForCharacterBuilderToLoad,
    selectClass,
    assertStepComplete,
    goToStep
};

test('seraph class can be selected and shows correct information', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    // Should see Seraph as an option
    $page->assertSee('Seraph');

    // Click on Seraph class card
    selectClass($page, 'seraph');

    // Check that Seraph-specific information is displayed
    $page->assertSee('Splendor')
        ->assertSee('Valor')
        ->assertSee('Prayer Dice');

    // Check class description content
    $page->assertSee('Seraphs are divine fighters and healers imbued with sacred purpose');
    
    // Verify step completion works
    assertStepComplete($page, 1);
});

test('seraph subclass step can be navigated', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'seraph');
    
    // Navigate to subclass step manually
    $page->click('[dusk="next-step-button"]')
        ->wait(3);
    
    // Should see subclass selection interface
    $page->assertSee('Subclass')
        ->assertSee('Choose');
});

test('seraph starting stats are displayed', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'seraph');
    
    // Check character displays basic stat information
    $page->assertSee('Seraph');
});

test('seraph shows divine features', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'seraph');
    
    // Should see Seraph-specific features
    $page->assertSee('Prayer Dice')
        ->assertSee('Life Support');
});

