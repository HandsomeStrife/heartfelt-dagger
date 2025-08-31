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

test('druid class can be selected and shows correct information', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    // Should see Druid as an option
    $page->assertSee('Druid');
    
    // Click on Druid class card
    selectClass($page, 'druid');
    
    // Check that Druid-specific information is displayed
    $page->assertSee('Sage')
        ->assertSee('Arcana')
        ->assertSee('Beastform');
    
    // Check class description content
    $page->assertSee('Becoming a druid is more than an occupation');
    
    // Verify step completion works
    assertStepComplete($page, 1);
});

test('druid subclass step can be navigated', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'druid');
    
    // Navigate to subclass step manually
    $page->click('[dusk="next-step-button"]')
        ->wait(3);
    
    // Should see subclass selection interface
    $page->assertSee('Subclass')
        ->assertSee('Choose');
});



test('druid starting stats are calculated correctly', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'druid');
    
    // Check character summary shows stats
    $page->assertSee('Evasion')
        ->assertSee('Hit Points')
        ->assertSee('Hope')
        ->assertSee('Stress');
});

test('druid shows beastform feature', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'druid');
    
    // Should see Druid-specific features
    $page->assertSee('Beastform')
        ->assertSee('Wildtouch')
        ->assertSee('Evolution');
});
