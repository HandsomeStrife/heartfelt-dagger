<?php

declare(strict_types=1);

require_once __DIR__ . '/../Helpers/CharacterBuilderHelpers.php';

use function Tests\Browser\Helpers\{
    waitForCharacterBuilderToLoad,
    selectClass,
    selectAncestry,
    selectCommunity
};

test('wanderborne community shows nomadic pack feature', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
    selectAncestry($page, 'human');
    
    selectCommunity($page, 'wanderborne');
    
    // Should see Wanderborne-specific features
    $page->assertSee('Wanderborne')
        ->assertSee('Hope');
});

test('wanderborne community nomadic pack shows item pulling mechanics', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
    selectAncestry($page, 'human');
    
    selectCommunity($page, 'wanderborne');
    
    // Should see the Nomadic Pack feature with item mechanics
    $page->assertSee('Hope')
        ->assertSee('mundane item');
});

test('wanderborne community shows correct description', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
    selectAncestry($page, 'human');
    
    selectCommunity($page, 'wanderborne');
    
    // Should see Wanderborne description
    $page->assertSee('Being part of a wanderborne community means you\'ve lived as a nomad');
});
