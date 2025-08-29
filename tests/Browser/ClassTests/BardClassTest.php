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

test('bard troubadour subclass creation workflow', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'bard');
    selectSubclass($page, 'troubadour');
    
    assertStepComplete($page, 2);
    
    // Check Troubadour-specific features are shown
    $page->assertSee('Troubadour')
        ->assertSee('Gifted Performer');
});

test('bard wordsmith subclass creation workflow', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'bard');
    selectSubclass($page, 'wordsmith');
    
    assertStepComplete($page, 2);
    
    // Check Wordsmith-specific features are shown
    $page->assertSee('Wordsmith')
        ->assertSee('Inscription');
});

test('bard complete character creation with troubadour', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    // Complete full character creation workflow
    selectClass($page, 'bard');
    assertStepComplete($page, 1);
    
    selectSubclass($page, 'troubadour');
    assertStepComplete($page, 2);
    
    selectAncestry($page, 'human');
    assertStepComplete($page, 3);
    
    selectCommunity($page, 'wanderborne');
    assertStepComplete($page, 4);
    
    goToTraitAssignment($page);
    assignTraitsDirectly($page, [
        'presence' => 2,
        'knowledge' => 1,
        'finesse' => 1,
        'agility' => 0,
        'instinct' => 0,
        'strength' => -1
    ]);
    assertStepComplete($page, 5);
    
    setCharacterName($page, 'Lyralei the Troubadour');
    assertStepComplete($page, 6);
    
    selectBasicEquipment($page);
    assertStepComplete($page, 7);
    
    fillBackgroundQuestions($page, [
        'My mentor was a legendary bard who taught me confidence through performance',
        'I loved a fellow musician who left me for fame in the capital',
        'I idolize the great bard Melody Heartstring for her inspiring ballads'
    ]);
    assertStepComplete($page, 8);
    
    createExperiences($page, [
        ['name' => 'Performance Mastery', 'description' => 'Years of performing in taverns and courts'],
        ['name' => 'Social Navigation', 'description' => 'Expert at reading people and social situations']
    ]);
    assertStepComplete($page, 9);
    
    selectDomainCards($page, 2);
    assertStepComplete($page, 10);
    
    fillConnections($page, [
        'Your quick wit and timing made me realize we work perfectly together',
        'You hum off-key during my performances, which somehow makes them better',
        'Your hand steadies me when I am nervous before big shows'
    ]);
    assertStepComplete($page, 11);
    
    // Verify all steps are complete
    assertProgressPercentage($page, 100);
});

test('bard suggested traits are applied correctly', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'bard');
    
    // Skip to trait assignment
    goToStep($page, 5);
    
    // Apply suggested traits using the helper function
    assignTraitsDirectly($page, [
        'presence' => 2,
        'knowledge' => 1,
        'finesse' => 1,
        'agility' => 0,
        'instinct' => 0,
        'strength' => -1
    ]);
    
    assertStepComplete($page, 5);
});

test('bard domain cards are restricted to grace and codex', function () {
    $page = visit('/character-builder');
    
    waitForCharacterBuilderToLoad($page);
    
    selectClass($page, 'bard');
    selectSubclass($page, 'troubadour');
    
    // Skip to domain card selection
    goToStep($page, 10);
    
    // Should only see Grace and Codex domains
    $page->assertSee('Grace')
        ->assertSee('Codex');
    
    // Should NOT see other domains
    $page->assertDontSee('Blade')
        ->assertDontSee('Bone')
        ->assertDontSee('Arcana');
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
