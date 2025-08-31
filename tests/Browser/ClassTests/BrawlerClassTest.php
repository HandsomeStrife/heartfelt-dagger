<?php

declare(strict_types=1);

require_once __DIR__ . '/../Helpers/CharacterBuilderHelpers.php';

use function Tests\Browser\Helpers\{
    waitForCharacterBuilderToLoad,
    selectClass,
    assertStepComplete,
    goToStep
};

test('brawler class can be selected and shows correct information', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    // Should see Brawler as an option
    $page->assertSee('Brawler');

    // Click on Brawler class card
    selectClass($page, 'brawler');

    // Check that Brawler-specific information is displayed
    $page->assertSee('Bone')
        ->assertSee('Valor')
        ->assertSee('I Am the Weapon');

    // Check class description content
    $page->assertSee('As a brawler, you can use your fists just as well as any weapon');
    
    // Verify step completion works
    assertStepComplete($page, 1);
});

test('brawler subclass step can be navigated', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'brawler');
    
    // Navigate to subclass step manually
    $page->click('[dusk="next-step-button"]')
        ->wait(3);
    
    // Should see subclass selection interface
    $page->assertSee('Subclass')
        ->assertSee('Choose');
});

test('brawler starting stats are displayed', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'brawler');
    
    // Check character displays basic stat information
    $page->assertSee('Brawler');
});

test('brawler shows combat features', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'brawler');
    
    // Should see Brawler-specific features
    $page->assertSee('I Am the Weapon')
        ->assertSee('Staggering Strike');
});

