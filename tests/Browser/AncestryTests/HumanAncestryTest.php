<?php

declare(strict_types=1);

require_once __DIR__ . '/../Helpers/CharacterBuilderHelpers.php';

use function Tests\Browser\Helpers\{
    waitForCharacterBuilderToLoad,
    selectClass,
    selectAncestry
};

test('human ancestry provides additional stress slot', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    // Select a class first to establish baseline stats
    selectClass($page, 'warrior');
    
    // Navigate to ancestry selection and select Human
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
        
    selectAncestry($page, 'human');
    
    // Should see Human-specific features
    $page->assertSee('Human')
        ->assertSee('High Stamina')
        ->assertSee('Adaptability');
    
    // Check that the extra stress slot is mentioned
    $page->assertSee('Stress');
});

test('human ancestry adaptability feature is displayed', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
        
    selectAncestry($page, 'human');
    
    // Should see the Adaptability feature description
    $page->assertSee('Adaptability')
        ->assertSee('When you fail a roll that utilized one of your Experiences, you can mark a Stress to reroll');
});

test('human ancestry shows correct description', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
        
    selectAncestry($page, 'human');
    
    // Should see Human description
    $page->assertSee('Humans are most easily recognized by their dexterous hands');
});

