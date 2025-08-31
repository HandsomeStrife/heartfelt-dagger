<?php

declare(strict_types=1);

require_once __DIR__ . '/../Helpers/CharacterBuilderHelpers.php';

use function Tests\Browser\Helpers\{
    waitForCharacterBuilderToLoad,
    selectClass,
    selectAncestry,
    selectCommunity
};

test('highborne community shows privilege feature', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
    selectAncestry($page, 'human');
    
    selectCommunity($page, 'highborne');
    
    // Should see Highborne-specific features
    $page->assertSee('Highborne')
        ->assertSee('advantage');
});

test('highborne community privilege shows advantage mechanics', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
    selectAncestry($page, 'human');
    
    selectCommunity($page, 'highborne');
    
    // Should see the Privilege feature with advantage mechanics
    $page->assertSee('advantage')
        ->assertSee('nobles');
});

test('highborne community shows correct description', function () {
    $page = visit('/character-builder');

    waitForCharacterBuilderToLoad($page);

    selectClass($page, 'warrior');
    
    $page->click('[dusk="next-step-button"]')
        ->wait(2);
    selectAncestry($page, 'human');
    
    selectCommunity($page, 'highborne');
    
    // Should see Highborne description
    $page->assertSee('Being part of a highborne community means you\'re accustomed to a life of elegance, opulence, and prestige');
});
