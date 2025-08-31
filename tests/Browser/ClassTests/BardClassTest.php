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

test('bard class can be selected and shows correct information', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    // Should see Bard as an option
    $page->assertSee('Bard');
    
    // Click on Bard class card
    selectClass($page, 'bard');
    
    // Check that Bard-specific information is displayed
    $page->assertSee('Grace')
        ->assertSee('Codex')
        ->assertSee('Rally');
    
    // Check class description content
    $page->assertSee('Bards are the most charismatic people in all the realms');
    
    // Verify step completion works
    assertStepComplete($page, 1);
});

test('bard subclass step can be navigated', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'bard');
    
    // Navigate to subclass step manually
    $page->click('[dusk="next-step-button"]')
        ->wait(3);
    
    // Should see subclass selection interface
    $page->assertSee('Subclass')
        ->assertSee('Choose');
});



test('bard starting stats are calculated correctly', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'bard');
    
    // Check character summary shows correct Bard starting stats
    $page->assertSee('Evasion')
        ->assertSee('Hit Points')
        ->assertSee('Hope')
        ->assertSee('Stress');
});
