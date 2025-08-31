<?php

declare(strict_types=1);

require_once __DIR__ . '/../Helpers/CharacterBuilderHelpers.php';

use function Tests\Browser\Helpers\{
    waitForCharacterBuilderToLoad,
    selectClass,
    selectAncestry,
    selectCommunity
};

test('seaborne community shows know the tide feature', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
    selectAncestry($page, 'human');
    
    selectCommunity($page, 'seaborne');
    
    // Should see Seaborne-specific features
    $page->assertSee('Seaborne')
        ->assertSee('Fear');
});

test('seaborne community know the tide shows token mechanics', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
    selectAncestry($page, 'human');
    
    selectCommunity($page, 'seaborne');
    
    // Should see the Know the Tide feature with token mechanics
    $page->assertSee('Fear')
        ->assertSee('token');
});

test('seaborne community shows correct description', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
    selectAncestry($page, 'human');
    
    selectCommunity($page, 'seaborne');
    
    // Should see Seaborne description
    $page->assertSee('Being part of a seaborne community means you lived on or near a large body of water');
});
