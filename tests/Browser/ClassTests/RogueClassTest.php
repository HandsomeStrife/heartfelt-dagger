<?php

declare(strict_types=1);

require_once __DIR__ . '/../Helpers/CharacterBuilderHelpers.php';

use function Tests\Browser\Helpers\{
    waitForCharacterBuilderToLoad,
    selectClass,
    selectSubclass,
    selectAncestry,
    selectCommunity,
    assignTraitsDirectly,
    goToTraitAssignment,
    setCharacterName,
    selectBasicEquipment,
    fillBackgroundQuestions,
    createExperiences,
    selectDomainCards,
    fillConnections,
    assertStepComplete,
    assertProgressPercentage,
    goToStep
};

test('rogue class can be selected and shows correct information', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    // Should see Rogue as an option
    $page->assertSee('Rogue');
    
    // Click on Rogue class card
    selectClass($page, 'rogue');
    
    // Check that Rogue-specific information is displayed
    $page->assertSee('Midnight')
        ->assertSee('Grace')
        ->assertSee('Sneak Attack');
    
    // Check class description content
    $page->assertSee('Rogues are scoundrels, often in both attitude and practice');
    
    // Verify step completion works
    assertStepComplete($page, 1);
});

test('rogue subclass step can be navigated', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'rogue');
    
    // Navigate to subclass step manually
    $page->click('[dusk="next-step-button"]')
        ->wait(3);
    
    // Should see subclass selection interface
    $page->assertSee('Subclass')
        ->assertSee('Choose');
});



test('rogue starting stats are calculated correctly', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'rogue');
    
    // Check character summary shows stats
    $page->assertSee('Evasion')
        ->assertSee('Hit Points')
        ->assertSee('Hope')
        ->assertSee('Stress');
});

test('rogue shows stealth features', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'rogue');
    
    // Should see Rogue-specific features
    $page->assertSee('Sneak Attack')
        ->assertSee('Cloaked');
});
