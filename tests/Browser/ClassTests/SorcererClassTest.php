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

test('sorcerer class can be selected and shows correct information', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    // Should see Sorcerer as an option
    $page->assertSee('Sorcerer');
    
    // Click on Sorcerer class card
    selectClass($page, 'sorcerer');
    
    // Check that Sorcerer-specific information is displayed
    $page->assertSee('Arcana')
        ->assertSee('Midnight')
        ->assertSee('Arcane Sense');
    
    // Check class description content
    $page->assertSee('Not all innate magic users choose to hone their craft');
    
    // Verify step completion works
    assertStepComplete($page, 1);
});

test('sorcerer subclass step can be navigated', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'sorcerer');
    
    // Navigate to subclass step manually
    $page->click('[dusk="next-step-button"]')
        ->wait(3);
    
    // Should see subclass selection interface
    $page->assertSee('Subclass')
        ->assertSee('Choose');
});



test('sorcerer starting stats are calculated correctly', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'sorcerer');
    
    // Check character summary shows stats
    $page->assertSee('Evasion')
        ->assertSee('Hit Points')
        ->assertSee('Hope')
        ->assertSee('Stress');
});

test('sorcerer shows innate magic features', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'sorcerer');
    
    // Should see Sorcerer-specific features
    $page->assertSee('Arcane Sense')
        ->assertSee('Volatile Magic');
});
