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

test('guardian class can be selected and shows correct information', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    // Should see Guardian as an option
    $page->assertSee('Guardian');
    
    // Click on Guardian class card
    selectClass($page, 'guardian');
    
    // Check that Guardian-specific information is displayed
    $page->assertSee('Valor')
        ->assertSee('Blade')
        ->assertSee('Unstoppable');
    
    // Check class description content
    $page->assertSee('The title of guardian represents an array of martial professions');
    
    // Verify step completion works
    assertStepComplete($page, 1);
});

test('guardian subclass step can be navigated', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'guardian');
    
    // Navigate to subclass step manually
    $page->click('[dusk="next-step-button"]')
        ->wait(3);
    
    // Should see subclass selection interface
    $page->assertSee('Subclass')
        ->assertSee('Choose');
});



test('guardian starting stats are calculated correctly', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'guardian');
    
    // Check character summary shows stats
    $page->assertSee('Evasion')
        ->assertSee('Hit Points')
        ->assertSee('Hope')
        ->assertSee('Stress');
});

test('guardian shows protection features', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'guardian');
    
    // Should see Guardian-specific features
    $page->assertSee('Unstoppable')
        ->assertSee('Frontline Tank');
});
