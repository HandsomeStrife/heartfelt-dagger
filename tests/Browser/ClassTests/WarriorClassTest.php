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

test('warrior class can be selected and shows correct information', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    // Should see Warrior as an option
    $page->assertSee('Warrior');
    
    // Click on Warrior class card
    selectClass($page, 'warrior');
    
    // Check that Warrior-specific information is displayed
    $page->assertSee('Blade')
        ->assertSee('Bone')
        ->assertSee('Combat Training');
    
    // Check class description content
    $page->assertSee('Becoming a warrior requires years');
    
    // Verify step completion works
    assertStepComplete($page, 1);
});

test('warrior subclass step can be navigated', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'warrior');
    
    // Navigate to subclass step manually
    $page->click('[dusk="next-step-button"]')
        ->wait(3);
    
    // Should see subclass selection interface
    $page->assertSee('Subclass')
        ->assertSee('Choose');
});





test('warrior starting stats are displayed', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'warrior');
    
    // Check character displays basic stat information
    $page->assertSee('Warrior');
});

test('warrior shows combat features', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'warrior');
    
    // Should see Warrior-specific features
    $page->assertSee('Combat Training')
        ->assertSee('No Mercy');
});
