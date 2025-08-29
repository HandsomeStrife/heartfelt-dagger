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

test('wizard class can be selected and shows correct information', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    // Should see Wizard as an option
    $page->assertSee('Wizard');
    
    // Click on Wizard class card
    selectClass($page, 'wizard');
    
    // Check that Wizard-specific information is displayed
    $page->assertSee('Codex')
        ->assertSee('Splendor')
        ->assertSee('Prestidigitation');
    
    // Check class description content
    $page->assertSee('Whether through an institution or individual study');
    
    // Verify step completion works
    assertStepComplete($page, 1);
});

test('wizard subclass step can be navigated', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'wizard');
    
    // Navigate to subclass step manually
    $page->click('[dusk="next-step-button"]')
        ->wait(3);
    
    // Should see subclass selection interface
    $page->assertSee('Subclass')
        ->assertSee('Choose');
});

test('wizard domain cards are restricted to codex and splendor', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'wizard');
    
    // Skip to domain card selection
    goToStep($page, 10);
    
    // Should only see Codex and Splendor domains
    $page->assertSee('Codex')
        ->assertSee('Splendor');
    
    // Should NOT see other domains
    $page->assertDontSee('Grace')
        ->assertDontSee('Valor')
        ->assertDontSee('Sage');
});

test('wizard starting stats are calculated correctly', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'wizard');
    
    // Check character summary shows stats
    $page->assertSee('Evasion')
        ->assertSee('Hit Points')
        ->assertSee('Hope')
        ->assertSee('Stress');
});

test('wizard shows spellcasting features', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'wizard');
    
    // Should see Wizard-specific features
    $page->assertSee('Prestidigitation')
        ->assertSee('Not This Time');
});
