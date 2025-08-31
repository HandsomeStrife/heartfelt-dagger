<?php

declare(strict_types=1);

require_once __DIR__ . '/../Helpers/CharacterBuilderHelpers.php';

use function Tests\Browser\Helpers\{
    waitForCharacterBuilderToLoad,
    selectClass,
    selectSubclass,
    selectAncestry,
    selectCommunity,
    setCharacterName
};

test('giant ancestry provides additional hit point slot', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    // Select a class first to establish baseline stats
    selectClass($page, 'warrior');
    
    // Navigate to ancestry selection and select Giant
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
        
    selectAncestry($page, 'giant');
    
    // Should see Giant-specific features
    $page->assertSee('Giant')
        ->assertSee('Endurance')
        ->assertSee('Reach');
});

test('giant ancestry reach feature is displayed', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
        
    selectAncestry($page, 'giant');
    
    // Should see the Reach feature description
    $page->assertSee('Reach')
        ->assertSee('Melee range');
});

test('giant ancestry shows correct description', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
        
    selectAncestry($page, 'giant');
    
    // Should see Giant description
    $page->assertSee('Giants are towering humanoids with broad shoulders');
});
